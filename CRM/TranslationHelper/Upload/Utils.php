<?php

class CRM_TranslationHelper_Upload_Utils {
  /**
   * Returns a list of supported file formats for upload.
   */
  static function getSupportedUploadFormats() {
    return array('Excel2007', 'OpenDocument', 'OOCalc');
  }

  /**
   * Extract the column headers (first row).
   * NB: we're not using all of $data[1], because it contains values from column 'A' to 'AMK' (1024+1 cols).
   *
   * @param Array &$data PHPExcel data
   * @returns Array key/val.
   */
  static function extractColumnHeaders(&$data) {
    $headers = array();

    foreach ($data[1] as $key => $val) {
      if ($val) {
        $headers[$key] = trim($val);
      }
    }

    return $headers;
  }
}
