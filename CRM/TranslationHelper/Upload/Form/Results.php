<?php

/**
 * Form controller class
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_TranslationHelper_Upload_Form_Results extends CRM_Core_Form {
  protected $_mapper = NULL;

  /**
   * @access public
   * @return void
   */
  function preProcess() {
    parent::preProcess();
  }

  /**
   * This function is used to build the form.
   *
   * @access public
   * @return void
   */
  function buildQuickForm() {
    CRM_Utils_System::setTitle(ts("Translation import result", array('domain' => 'coop.symbiotic.translationhelper')));

    $errors = $this->controller->get('import_fails');

    if (empty($errors)) {
      $errors = 0;
    }

    $this->assign('import_info_messages', $message);
    $this->assign('import_success', $this->controller->get('import_success'));
    $this->assign('import_fails', $this->controller->get('import_fails'));
    $this->assign('import_error_messages', $this->controller->get('import_error_messages'));

    parent::buildQuickForm();
  }

  static function importFinished(CRM_Queue_TaskContext $ctx) {
    CRM_Core_Error::debug_log_message('finished task');
  }

}
