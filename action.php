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
    $controller->register_hook('ACTION_ACT_PREPROCESS', 'AFTER', $this, 'skipLoginAction', NULL);
    $controller->register_hook('HTML_LOGINFORM_OUTPUT', 'BEFORE', $this, 'handleOldLoginForm'); // deprecated
    $controller->register_hook('FORM_LOGIN_OUTPUT', 'BEFORE', $this, 'handleLoginForm');
  }

  function print_login_unavailable_msg() {
    msg("Legacy login form not available when using external authentication");
  }
  
  /**
   * Event handler to skip the 'login' action
   */
  function skipLoginAction(&$event, $param) {
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
    public function handleOldLoginForm(Doku_Event $event, $param)
    {
        global $ID;
        global $conf;
        if ($conf['authtype'] != 'authjwt') return;

        $this->print_login_unavailable_msg();

        $event->data = new Doku_Form(array());
        $event->data->addElement('<a href="' . wl($ID, array('do' => 'login')) . '">Login here</a>');
    }

    public function handleLoginForm(Doku_Event $event)
    {
        global $ID;
        global $conf;
        if ($conf['authtype'] != 'authjwt') return;

        /** @var Form $form */
        $form = $event->data;

        // remove login form
        do {
            $form->removeElement(0);
        } while ($form->elementCount() > 0);

        //$event->data->addHTML('<a href="' . wl($ID, array('do' => 'login')) . '">Login here</a>');
        $form->addFieldsetClose();
    }
}
