<?php

/**
 * Form controller class
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_TranslationHelper_Upload_Form_MapFields extends CRM_Core_Form {
  const QUEUE_NAME = 'translationhelper';
  public $_headers = array();
  public $_languages = array();

  public $_warnings = array();
  public $_errors = array();

  /**
   * This function is called before buildForm. Any pre-processing that
   * needs to be done for buildForm should be done here.
   *
   * @access public
   * @return void
   */
  function preProcess() {
    parent::preProcess();

    // Check if the user tried to go back after finishing the upload.
    // Resend back to the first step.
    if (! $this->controller->get('tableName')) {
      $this->controller->resetPage('DataUpload');
    }

    $this->getFileColumnHeaders();
    $this->getLanguagesFromHeaders();
  }

  /**
   * Implements setDefaultValues() for QuickForm.
   * (not used)
   */
  function setDefaultValues() {
    $defaults = array();
    return $defaults;
  }

  /**
   * This function is used to build the form.
   *
   * @access public
   * @return void
   */
  function buildQuickForm() {
    CRM_Utils_System::setTitle(ts("Translation import"));

    $this->assign('errors', $this->_errors);
    $this->assign('warnings', $this->_warnings);

    $this->addCheckBox('languages', ts('Languages to import'),
      array_flip($this->_languages),
      NULL, NULL, NULL, NULL,
      array('&nbsp;&nbsp;', '&nbsp;&nbsp;', '&nbsp;&nbsp;', '<br/>')
    );

    if (count($this->_errors)) {
      $this->addButtons(array(
        array(
          'type' => 'back',
          'name' => ts('Previous'),
          'isDefault' => TRUE,
        ),
      ));
    }
    else {
      $this->addButtons(array(
        array(
          'type' => 'back',
          'name' => ts('Previous'),
          'isDefault' => FALSE,
        ),
        array(
          'type' => 'next',
          'name' => ts('Next >>'),
          'isDefault' => TRUE,
        ),
      ));
    }

    // Export form elements
    $this->assign('elementNames', $this->controller->getRenderableElementNames($this));
    parent::buildQuickForm();
  }

  /**
   * Data validation after submit.
   */
  function validate() {
    $values = $this->exportValues();
    $valid = TRUE;

    if (empty($values['languages'])) {
      $this->setElementError('languages', ts("Please select at least one language to import."));
      $valid = FALSE;
    }

    return $valid;
  }

  /**
   * This function is called after the form is validated. Any
   * processing of form state etc should be done in this function.
   * Typically all processing associated with a form should be done
   * here and relevant state should be stored in the session
   *
   * @access public
   * @return void
   */
  function postProcess() {
    $values = $this->exportValues();
    $queue_name = self::QUEUE_NAME . '-' . time();

    $queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => $queue_name,
      'reset' => FALSE,
    ));

    $error_messages = array();
    $count = $this->processAllItems($values, $queue, $error_messages);

    $this->controller->set('import_success', $count);
    $this->controller->set('import_error_messages', $error_messages);
    $this->controller->set('import_is_running', TRUE);

    $runner = new CRM_Queue_Runner(array(
      'title' => ts('CiviCRM translation import'),
      'queue' => $queue,
      'errorMode' => CRM_Queue_Runner::ERROR_ABORT,
      'onEnd' => array('CRM_TranslationHelper_Upload_Form_Results', 'importFinished'),
      'onEndUrl' => CRM_Utils_System::url('civicrm/translation/import', array('qfKey' => $_REQUEST['qfKey'], '_qf_Results_display' => 'true')),
    ));

    // does not return
    $runner->runAllViaWeb();

    parent::postProcess();
  }

  /**
   * Takes all the temporarely stored DB rows and moves them into the CiviCRM queue.
   *
   * This probably sounds a bit overkill, but the initial code pre-processed the
   * uploaded data before moving it into the queue (where we queue basically API calls).
   */
  function processAllItems(&$values, &$queue, &$error_messages) {
    $count = 0;
    $headers = $this->controller->get('headers');

    // Map original headers to those we really need.
    $find = array('entity_type', 'entity_id', 'field_id');
    $map_headers = array();

    foreach ($find as $f) {
      $x = array_search($f, $headers);
      $map_headers[$f] = strtolower($x);
    }

    foreach ($values['languages'] as $key => $val) {
      $f = 'value_' . $key;
      $x = array_search($f, $headers);

      // nb: we store 'value_en_US' as just 'en_US', because ..
      // that's how the TranslationString.translate API was written initially for inline edit.
      $map_headers[$key] = strtolower($x);
    }

    // For each row of the uploaded document,
    // - validate basic mandatory fields and catch easy errors (the API may catch more later).
    // - do an API call to create the data.
    $dao = CRM_Core_DAO::executeQuery('SELECT * FROM ' . $this->controller->get('tableName'));

    while ($dao->fetch()) {
      $params = array();

      foreach ($map_headers as $field_name => $orig_header) {
        $params[$field_name] = $dao->$orig_header;
      }

      // This lets mapping classes inject more data at the last second.
      // Ex: the DonsParDiocese adds the source_record_id using the diocese value.
      // NB: we send controller values, for some default values such as 'campaign', 'campaign_year'.
      $controller_values = $this->controller->exportValues();

      // Check for mandatory fields.
      $valid = TRUE;
/* TODO
      foreach ($fields as $fkey => $fval) {
        if ($fval['mandatory'] && ! isset($params[$fkey])) {
          $valid = FALSE;
          $error_messages[] = ts("Ligne %1: n'a pas de champ %2 (ou aucune valeur correspondante trouvée).", array(1 => $dao->row, 2 => $fval['label']));
        }
        if (! empty($params[$fkey]) && isset($fval['validate'])) {
          if (! CRM_Utils_Type::validate($params[$fkey], $fval['validate'], FALSE)) {
            $valid = FALSE;
            $error_messages[] = ts("Ligne %1: le champ %2 n'a pas le bon format de données (%3)", array(1 => $dao->row, 2 => $fval['label'], 3 => $fval['validate']));
          }
        }
      }
*/

      $queue->createItem(new CRM_Queue_Task(
        array('CRM_TranslationHelper_Upload_Form_MapFields', 'processItem'),
        array($params),
        ts("Task %1", array(1 => $count))
      ));

      $count++;
    }

    $dao->free();

    // We can now delete the temporary table.
    CRM_Core_DAO::executeQuery('DROP TABLE ' . $this->controller->get('tableName'));
    $this->controller->set('tableName', NULL);

    return $count;
  }

  /**
   * Gets the file column headers and validates for mandatory fields.
   */
  function getFileColumnHeaders() {
    if (empty($this->_headers)) {
      $this->_headers = $this->controller->get('headers');
    }

    $mandatory = array('entity_type', 'entity_id', 'field_id');

    foreach ($mandatory as $key => $val) {
      if (! in_array($val, $this->_headers)) {
        $this->_errors[] = ts('The file is missing the column: "%1".', array(1 => $val));
      }
    }
  }

  /**
   * Checks for columns named, for example, "value_fr_FR", "value_en_US", and
   * stores in the $this->_languages array, ex: [ 'fr_FR' => 'Francais', 'en_US' => 'English' ].
   */
  function getLanguagesFromHeaders() {
    $this->_languages = array();
    $allowed = CRM_TranslationHelper_BAO_FindStrings::enabledLanguages();

    if (empty($this->_headers)) {
      $this->_headers = $this->controller->get('headers');
    }

    foreach ($this->_headers as $key => $val) {
      if (substr($val, 0, 6) == 'value_') {
        $lang = substr($val, 6);

        if (isset($allowed[$lang])) {
          $this->_languages[$lang] = $allowed[$lang];
        }
        else {
          $this->_warnings[] = ts("The uploaded file had translations for %1, but this language is not currently enabled. It will be ignored.", array(1 => $lang));
        }
      }
    }
  }

  /**
   * Callback for the queue runner. This does the actual import.
   */
  static function processItem(CRM_Queue_TaskContext $ctx, $params, $message = NULL) {
    // NB: this might throw an exception, but depending on the errorMode
    // provided to the Queue Runner, it will either prompt or ignore.
    try {
      $result = civicrm_api3('TranslationString', 'translate', $params);

      if (! empty($result['is_error'])) {
        return FALSE;
      }
    }
    catch (Exception $e) {
      // Log and throw back for errorMode handling.
      $ctx->log->log('Import: ' . $e->getMessage());
      throw $e;
    }

    return TRUE;
  }

}
