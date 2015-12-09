<?php

/**
 * @file
 *
 * Helper drush module to import localized CiviCRM configuration strings from a file.
 */

/**
 * Implementation of hook_drush_command().
 */
function translationhelper_drush_command() {
  $items = array();

  $items['translationhelper-importfile'] = array(
    'description' => 'Import localized CiviCRM configuration strings from a file (OpenDocument ODS or Excel 2007)',
    'arguments' => array(
      'file' => 'File to import',
      'languages' => 'Comma-separated list of languages to import. Ex: "en_US,fr_CA".',
    ),
  );

  return $items;
}

/**
 * Implements drush_hook_command().
 *
 * @param String $file Filename to import.
 * @param String $languages Comma-separated list of languages to import. Ex: "en_US,fr_CA"
 */
function drush_translationhelper_importfile($file, $languages) {
  $languages = explode(',', $languages);

  if (empty($languages)) {
    return drush_set_error('TRANSLATIONHELPER_INVALID_LANG', dt("Please specificy languages to import."));
  }

  include('vendor/phpoffice/phpexcel/Classes/PHPExcel.php');

  // Validate uploaded file type.
  // This is a bit redundant with code in CRM/TranslationHelper/Upload/Form/DataUpload.php validate().
  $file_type = PHPExcel_IOFactory::identify($file);
  $supported_formats = CRM_TranslationHelper_Upload_Utils::getSupportedUploadFormats();

  if (! in_array($file_type, $supported_formats)) {
    return drush_set_error('TRANSLATIONHELPER_INVALID_FILE', dt("!file: not in a valid format (!format).", array('!file' => $file, '!format' => $file_type)));
  }

  $file_type = PHPExcel_IOFactory::identify($file);
  $objReader = PHPExcel_IOFactory::createReader($file_type);
  $objReader->setReadDataOnly(TRUE);

  $objPHPExcel = $objReader->load($file);

  // Iterate through each sheet in the file.
  foreach ($objPHPExcel->getAllSheets() as $sheet) {
    drush_log(dt("Importing sheet: !name", array('!name' => $sheet->getTitle())));

    $cpt = 0;
    $data = $sheet->toArray(NULL, TRUE, TRUE, TRUE);

    // Headers: A => 'entity_type', B => 'entity_id', etc.
    // Flip: 'entity_type' => A, 'entity_id' => B, etc.
    $headers = CRM_TranslationHelper_Upload_Utils::extractColumnHeaders($data);
    $flip = array_flip($headers);

    unset($data[1]);

    $mandatory = array('entity_type', 'entity_id', 'field_id', 'field_title');

    // Iterate through each row of this sheet.
    foreach ($data as $row) {
      $params = array();

      foreach ($mandatory as $m) {
        // TODO: check for missing values? (the API checks too..)
        $params[$m] = $row[$flip[$m]];
      }

      foreach ($languages as $lang) {
        $params[$lang] = $row[$flip['value_' . $lang]];
      }

      try {
        civicrm_api3('TranslationString', 'Translate', $params);
      }
      catch (Exception $e) {
        drush_log($sheet->getTitle() . ' row ' . $cpt . ': FAILED: ' . $e->getMessage(), 'error');
      }

      $cpt++;
    }
  }
}
