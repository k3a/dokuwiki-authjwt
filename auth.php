<?php
/**
 * DokuWiki plugin
 *
 * @license MIT
 */


// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class auth_plugin_authjwt extends DokuWiki_Auth_Plugin {

  /**
   * Constructor.
   */
  public function __construct() {
    parent::__construct();
    
    /* Supports logOff() method */
    $this->cando['logout'] = true;
    /* This plugins uses it's own authentication. */
    $this->cando['external'] = true;
    
    $this->success = false;

    require_once __DIR__ . "/jwt-authorizer/authorizer.php";
    
    /* Load the config */
    $this->loadConfig();

    try {
      if (empty($this->getConf("issuers"))) {
        throw new Exception("config parameter 'issuers' must be a non-empty array of valid OIDC issuer domains");
      }

      if (empty($this->getConf("cachekey"))) {
        throw new Exception("config parameter 'cachekey' must be set to a non-empty string");
      }
    }
    catch (Exception $e) {
      msg($e->getMessage());
      return;
    }

    $this->success = true;
  }

  public function trustExternal($user, $pass, $sticky=false) {
    global $USERINFO;

    if (!$this->success) {
      return false;
    }

    $jav = new JWTAuthVerifier($this->getConf("issuers"), $this->getConf("cachekey"));

    if (!$jav->hasToken()) {
      // no token provided
      return false;
    }

    if (!$jav->verify()) {
      msg("Provided authentication token is not valid");
      return false;
    }

    // check required group (if configured)
    $required_group = $this->getConf("required_group");
    if ($required_group != "" && !$jav->verifyGroup($required_group)) {
      // token invalid or group not present in the token
      msg("Provided authentication token doesn't contain required group '$required_group'");
      return false;
    }
    
    // extract user id from the email
    $userid = explode("@", $jav->email)[0];
    if ($userid == "") {
      // no user email provided
      return false;
    }
    
    // fill in session info
    $USERINFO["name"] = $jav->userName;
    $USERINFO["mail"] = $jav->email;
    $USERINFO["grps"] = $jav->mapGroups($this->getConf("map_groups"), true);
    
    $_SERVER["REMOTE_USER"]                = $userid;
    $_SESSION[DOKU_COOKIE]["auth"]["user"] = $userid;
    $_SESSION[DOKU_COOKIE]["auth"]["info"] = $USERINFO;
    
    return true;
  }

  public function logOff() {
    // unset session data
    if (isset($_SESSION[DOKU_COOKIE]["auth"]["user"])) {
      unset($_SESSION[DOKU_COOKIE]["auth"]["user"]);
    }
    if (isset($_SESSION[DOKU_COOKIE]["auth"]["info"])) {
        unset($_SESSION[DOKU_COOKIE]["auth"]["info"]);
    }

    // clear cookies
    setcookie("_admin_auth", "", time() - 3600);
    setcookie("_admin_auth_user_name", "", time() - 3600);

    // redirect to home
    if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/') {
        send_redirect(DOKU_URL);
    }
  }

}
