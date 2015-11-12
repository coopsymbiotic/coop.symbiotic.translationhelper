<?php

class CRM_TranslationHelper_BAO_Export_Excel2007 {
  /**
   * Force the download of the generated file.
   */
  function download_headers($filename, $description) {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    header('Content-Description: ' . $description);
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
  }

  /**
   *
   */
  function createWriter(&$objPHPExcel, $filename) {
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($filename);
  }
}
