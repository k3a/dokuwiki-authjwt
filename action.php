<?php
/**
 * DokuWiki plugin
 *
 * @license MIT
 */


// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_authjwt extends DokuWiki_Action_Plugin {
  
  function register(Doku_Event_Handler $controller){
    $controller->register_hook('ACTION_ACT_PREPROCESS', 'AFTER', $this, 'skip_login_action', NULL);
    $controller->register_hook('HTML_LOGINFORM_OUTPUT', 'BEFORE', $this, 'handle_loginform');
  }

  function print_login_unavailable_msg() {
    msg("Legacy login form not available when using external authentication");
  }
  
  /**
   * Event handler to skip the 'login' action
   */
  function skip_login_action(&$event, $param) {
    /* Some actions handled in inc/actions.php:act_dispatch() result in $ACT
       being modified to 'login', eg. 'register'. */
    if($event->data == 'login') {
        $this->print_login_unavailable_msg();
    }
  }

   /**
     * Disable the login forma and instead use a link to trigger login
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function handle_loginform(Doku_Event $event, $param)
    {
        global $ID;
        global $conf;
        if ($conf['authtype'] != 'authjwt') return;

        $this->print_login_unavailable_msg();

        $event->data = new Doku_Form(array());
        //$event->data->addElement('<a href="' . wl($ID, array('do' => 'login')) . '">Login here</a>');
    }
}
