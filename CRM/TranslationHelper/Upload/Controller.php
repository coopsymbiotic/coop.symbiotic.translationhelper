<?php

/**
 * Controller for the multi-form process to upload data.
 * Mostly based off CRM_Import_Controller/CRM_Contact_Import_Controller.
 */
class CRM_TranslationHelper_Upload_Controller extends CRM_Core_Controller {
  function __construct($title = NULL, $action = CRM_Core_Action::NONE, $modal = TRUE) {
    parent::__construct($title, $modal);

    $this->_stateMachine = new CRM_TranslationHelper_Upload_StateMachine($this, $action);

    // Workaround because the queue was redirecting and escaping the parameters,
    // so only managed to redirect to civicrm/translation/import?qfKey=[...], but
    // missing &_qf_Results_dispaly=true. Therefore redirecting this way, and it
    // correctly sends to the last step of the wizard.
    if ($this->get('import_is_running')) {
      $this->set('import_is_running', FALSE);
      $url = CRM_Utils_System::url('civicrm/translation/import', array('qfKey' => $_REQUEST['qfKey'], '_qf_Results_display' => 'true'));
      CRM_Utils_System::redirect($url);
    }

    // Create and instantiate the pages
    $this->addPages($this->_stateMachine, $action);

    // Add all the actions
    $this->addActions();
  }

  /**
   * Get the fields/elements defined in a form.
   * (called by individual forms, but placed here to avoid code duplication)
   *
   * @return array (string)
   */
  function getRenderableElementNames(&$form) {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($form->_elements as $element) {
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
