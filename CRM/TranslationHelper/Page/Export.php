<?php

/**
 * @file
 *
 * Handles the generation of a spreadsheet file using an array of data.
 * Do not call this class directly. Call one of the implementations (Excel2007, ODS).
 */

class CRM_TranslationHelper_Page_Export extends CRM_Core_Page {
  protected $_helper = NULL;

  function run() {
    CRM_Utils_System::setTitle(ts('Export translation strings', ['domain' => 'coop.symbiotic.translationhelper']));

    $params = array();
    $params['language'] = CRM_Utils_Request::retrieve('language', 'String');
    $params['entity'] = CRM_Utils_Request::retrieve('entity', 'String');

    $implementations = array(
      'excel2007' => 'CRM_TranslationHelper_BAO_Export_Excel2007',
      'ods' => 'CRM_TranslationHelper_BAO_Export_OpenDocument',
    );

    $format = CRM_Utils_Request::retrieve('format', 'String');
    $format = strtolower($format);

    if (empty($format)) {
      $format = 'ods';
    }

    if (! empty($implementations[$format])) {
      $values = CRM_TranslationHelper_BAO_FindStrings::find($params);

      $c = $implementations[$format];
      $this->_helper = new $c;
      $this->export2excel2007($values);
    }
    else {
      CRM_Core_Error::fatal(ts("Unsupported output format: %1.", array(1 => $format, 'domain' => 'coop.symbiotic.translationhelper')));
    }
  }

  /**
   * Generates a XLS 2007 file and forces the browser to download it (when
   * running with default save to stdout).
   *
   * NB: $data is restructured to have one entity_type per sheet.
   *
   * @param Array &$data
   *   Data to export to Excel.
   *
   * @param String $filename
   *   Where to save the file. Defaults to standard output.
   *   Forces a download and calls civiExit() if saving to stdout.
   */
  function export2excel2007(&$data, $filename = 'php://output') {
    if ($filename == 'php://output') {
      // Force a download and name the file using the current timestamp.
      // nb: do not confuse $filename (stdout) and dlfilename.
      // also, we do not include the file extension, since it is set by the helper.
      $datetime = date('Ymd-Gi', $_SERVER['REQUEST_TIME']);
      $dlfilename = 'CiviCRM_Translations_' . $datetime;
      $description = ts('CiviCRM translation strings', ['domain' => 'coop.symbiotic.translationhelper']);

      $this->_helper->download_headers($dlfilename, $description);
    }

    $csv = '';

    // Generate an array with { 0=>A, 1=>B, 2=>C, ... }
    $foo = array(0 => '', 1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L', 13 => 'M');
    $a = ord('A');
    $cells = array();

    for ($i = 0; $i < count($foo); $i++) {
      for ($j = 0; $j < 26; $j++) {
        $cells[$j + ($i * 26)] = $foo[$i] . chr($j + $a);
      }
    }

    include('vendor/phpoffice/phpexcel/Classes/PHPExcel.php');
    $objPHPExcel = new PHPExcel();

    // Set document properties
    $objPHPExcel->getProperties()
      ->setCreator("CiviCRM")
      ->setLastModifiedBy("CiviCRM")
      ->setTitle(ts('Translation strings'))
      ->setSubject(ts('Translation strings'))
      ->setDescription(ts('Translation strings'));

    $splitdata = $this->splitDataPerEntityType($data);
    $sheet_counter = 0;

    foreach ($splitdata as $entity_type => &$rows) {
      if ($sheet_counter > 0) {
        $objWorkSheet = $objPHPExcel->createSheet($sheet_counter);
      }

      $sheet = $objPHPExcel->setActiveSheetIndex($sheet_counter);
      // assuming that the first column of the first row has the entity name
      // ex: ContributionPage
      $sheetname = $entity_type;
      $objPHPExcel->getActiveSheet()->setTitle($sheetname);

      // Add headers if this is the first row.
      $headers = array_keys($rows[0]);

      $col = 0;
      $cpt = 1;

      foreach ($headers as $h) {
        $objPHPExcel->getActiveSheet()
          ->setCellValue($cells[$col] . $cpt, $h);

        $col++;
      }

      // Add rows.
      $cpt = 2;

      foreach ($rows as $row) {
        $col = 0;

        foreach ($headers as $h) {
          $value = CRM_Utils_Array::value($h, $row);

          if (empty($value)) {
            $col++;
            continue;
          }

          // FIXME
          $value = html_entity_decode($value);

          $objPHPExcel->getActiveSheet()
            ->setCellValue($cells[$col] . $cpt, $value);

          $col++;
        }

        $cpt++;
      }

      $sheet_counter++;
    }

    // When done, set the first sheet as the active sheet.
    // No particular reason. It's just a bit weird otherwise.
    $objPHPExcel->setActiveSheetIndex(0);

    // Save or output the file.
    $this->_helper->createWriter($objPHPExcel, $filename);

    if ($filename == 'php://output') {
      CRM_Utils_System::civiExit();
    }
  }

  /**
   * Split the rows per entity type, so that we can more easily generate
   * one sheet per entitytype.
   * 
   * For example:
   *
   * ContributionPage,B,C,D,E
   * ContributionPage,F,G,H,I
   * Event,J,K,L,M
   *
   * Becomes:
   *
   * ContributionPage
   * - ContributionPage,B,C,D,E
   * - ContributionPage,F,G,H,I
   * Event
   * - Event,J,K,L,M
   */
  function splitDataPerEntityType(&$rows) {
    $new = array();

    foreach ($rows as &$row) {
      $t = $row['entity_type'];

      if (! isset($new[$t])) {
        $new[$t] = array();
      }

      $new[$t][] = $row;
    }

    return $new;
  }

}
