<?php

/**
 * State machine for managing different states of the Import process.
 * Mostly based on CRM_Import_StateMachine.
 */
class CRM_TranslationHelper_Upload_StateMachine extends CRM_Core_StateMachine {
  /**
   * Class constructor
   *
   * @param object  CRM_TranslationHelper_Upload_Controller
   * @param int     $action
   */
  function __construct($controller, $action = CRM_Core_Action::NONE) {
    parent::__construct($controller, $action);

    $classType = str_replace('_Controller', '', get_class($controller));

    $this->_pages = array(
      $classType . '_Form_DataUpload' => NULL,
      $classType . '_Form_MapFields' => NULL,
      $classType . '_Form_Results' => NULL,
    );

    $this->addSequentialPages($this->_pages, $action);
  }

}
