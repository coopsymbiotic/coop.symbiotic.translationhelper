<?php

/**
 * Form controller class
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_TranslationHelper_Upload_Form_DataUpload extends CRM_Core_Form {

  /**
   * This function is called before buildForm. Any pre-processing that
   * needs to be done for buildForm should be done here.
   *
   * @access public
   * @return void
   */
  function preProcess() {
    include('vendor/phpoffice/phpexcel/Classes/PHPExcel.php');

    // Check if the "stop" button of a queue was clicked.
    $this->flushQueueIfRequested();

    parent::preProcess();
  }

  /**
   * This function is used to build the form.
   *
   * @access public
   * @return void
   */
  function buildQuickForm() {
    CRM_Core_Resources::singleton()->addStyleFile('coop.symbiotic.translationhelper', 'bower_components/fontawesome/css/font-awesome.min.css');
    CRM_Utils_System::setTitle($this->ts("Import translations"));

    // Setting Upload File Size
    $config = CRM_Core_Config::singleton();
    $uploadSize = round(($config->maxFileSize / (1024 * 1024)), 2);

    $this->add('file', 'uploadFile', $this->ts('File'), 'size=30 maxlength=255', TRUE);
    $this->addRule('uploadFile', $this->ts('A valid file must be uploaded.'), 'uploadedfile');

    // Check the number of items currently in the queue.
    $this->addQueueStatus();

    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => $this->ts('Next >>'),
        'isDefault' => TRUE,
      ),
    ));

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

    // File type must be OpenDocument (ods) or Excel2007 (xlsx).
    // Some tests with Excel5 (xls) had mixed results, avoid.
    $e = $this->controller->_pages['DataUpload']->getElement('uploadFile');
    $file = $e->getValue();
    $tmp_file = $file['tmp_name'];
    $file_type = PHPExcel_IOFactory::identify($tmp_file);

    $supported_formats = CRM_TranslationHelper_Upload_Utils::getSupportedUploadFormats();

    if (! in_array($file_type, $supported_formats)) {
      $this->setElementError('uploadFile', $this->ts("The uploaded file must be in either Excel2007 (xlsx) or OpenDocument (ods) format. Detected format: %1.", array(1 => $file_type)));
      $valid = FALSE;
    }

    return $valid;
  }

  /**
   * Processing of the submitted form.
   *
   * @access public
   * @return void
   */
  function postProcess() {
    $values = $this->controller->exportValues();

    $e = $this->controller->_pages['DataUpload']->getElement('uploadFile');
    $file = $e->getValue();

    $tmp_file = $file['tmp_name'];

    // Import into a temp DB table
    $file_type = PHPExcel_IOFactory::identify($tmp_file);
    $objReader = PHPExcel_IOFactory::createReader($file_type);
    $objReader->setReadDataOnly(TRUE);

    // FIXME for each sheet.
    $objPHPExcel = $objReader->load($tmp_file);
    $data = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

    $this->saveToDatabaseTable($data);

    parent::postProcess();
  }

  /**
   * If a 'request' parameter has been provided with, ex: ?flush=[queue-name],
   * it will reset that queue (ex: to interrupt an import with errors).
   */
  function flushQueueIfRequested() {
    $flush = CRM_Utils_Request::retrieve('flush', 'String', $this);

    if (! $flush) {
      return;
    }

    $queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => $flush,
      'reset' => FALSE,
    ));

    if ($cpt = $queue->numberOfItems()) {
      $queue->deleteQueue();
      CRM_Core_Session::setStatus($this->ts('The task %1 with %2 items was cancelled.', array(1 => $flush, 2 => $cpt)));
    }
  }

  /**
   * Checks for active queues, and assigns the status to smarty variables.
   */
  function addQueueStatus() {
    $active_queues = array();
    $dao = CRM_Core_DAO::executeQuery('SELECT queue_name, submit_time, count(*) as cpt FROM civicrm_queue_item GROUP BY queue_name');

    while ($dao->fetch()) {
      // CiviCRM only allows to continue running queues started by the same user.
      // This check comes from CRM/Queue/Runner.php
      $may_run = (!empty($_SESSION['queueRunners'][$dao->queue_name]));

      $active_queues[] = array(
        'queue_name' => $dao->queue_name,
        'submit_time' => $dao->submit_time,
        'items' => $dao->cpt,
        'may_run' => $may_run,
      );
    }

    $this->assign('active_queues', $active_queues);
    $this->assign('active_queues_count', count($active_queues));
  }

  /**
   * Extracts the headers/columns from the data and creates a new SQL table,
   * then stores the data in it.
   */
  function saveToDatabaseTable(&$data) {
    $dao = new CRM_Core_DAO();
    $db = $dao->getDatabaseConnection();

    $tableName = 'civicrm_transhelper_' . md5(uniqid(rand(), TRUE));
    $this->controller->set('tableName', $tableName);

    $headers = CRM_TranslationHelper_Upload_Utils::extractColumnHeaders($data);
    $this->controller->set('headers', $headers);

    if (! count($headers)) {
      CRM_Core_Session::setStatus($this->ts("There was a problem analysing the uploaded file, or it was empty. Please verify the file and try again."));
      $this->controller->resetPage('DataUpload');
      return;
    }

    //
    // Create the temp table (it's not really temp, we will need to delete it when finished).
    //
    $columns = array();
    $columns[] = "`row` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique row ID' ";

    foreach ($headers as $key => $val) {
      $columns[] = '`' . strtolower($key) . '` TEXT DEFAULT NULL';
    }

    $columns[] = 'PRIMARY KEY (`row`)';

    $createSql .= implode(', ', $columns);

    $db->query("DROP TABLE IF EXISTS $tableName");
    $db->query("CREATE TABLE $tableName ( $createSql ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci");

    //
    // Insert the data (except the first row, it has headers).
    // NB: for now, we're assuming that all data are string (which, of course, is not true.. but easier for now).
    //
    unset($data[1]);

    $base_sql = 'INSERT INTO `' . $tableName . '` (' . implode(',', array_map('strtolower', array_keys($headers))) . ') VALUES';

    foreach ($data as $key => $val) {
      $cols = array();
      $params = array();
      $cpt = 0;

      // Sometimes phpexcel can return us some empty rows.
      // This will be true if we found some valid data in a cell.
      $data_found = FALSE;

      foreach ($headers as $kk => $vv) {
        $cols[] = '%' . $cpt;

        // NULL values cause the String validation to do a fatal exception.
        if (isset($val[$kk])) {
          $params[$cpt] = array($val[$kk], 'String');
          $data_found = TRUE;
        }
        else {
          $params[$cpt] = array('', 'String');
        }

        $cpt++;
      }

      if ($data_found) {
        CRM_Core_DAO::executeQuery($base_sql . '(' . implode(', ', $cols) . ')', $params);
      }
    }
  }

  function ts($string, $params = array()) {
    $params['domain'] = 'coop.symbiotic.translationhelper';
    return ts($string, $params);
  }

}
