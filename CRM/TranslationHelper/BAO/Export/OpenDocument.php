<?php

class CRM_TranslationHelper_BAO_Export_OpenDocument {
  /**
   * Force the download of the generated file.
   */
  function download_headers($filename, $description) {
    header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.ods"');
    header('Content-Description: ' . $description);
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
  }

  /**
   *
   */
  function createWriter(&$objPHPExcel, $filename) {
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'OpenDocument');
    $objWriter->save($filename);
  }
}
