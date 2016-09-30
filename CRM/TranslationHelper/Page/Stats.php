<?php

class CRM_TranslationHelper_Page_Stats extends CRM_Core_Page {
  function run() {
    CRM_Utils_System::setTitle(ts('Translation Statistics', ['domain' => 'coop.symbiotic.translationhelper']));

    $output = '';
    $result = civicrm_api3('Transifex', 'status');

    // FIXME: use tpl
    $output .= '<table>';

    $headers = [
      ts('Resource', ['domain' => 'coop.symbiotic.translationhelper']),
      ts('Translated', ['domain' => 'coop.symbiotic.translationhelper']),
      ts('Reviewed', ['domain' => 'coop.symbiotic.translationhelper']),
      ts('Untranslated strings', ['domain' => 'coop.symbiotic.translationhelper']),
      ts('Total strings', ['domain' => 'coop.symbiotic.translationhelper']),
      ts('Last update', ['domain' => 'coop.symbiotic.translationhelper']),
      ts('Updated by', ['domain' => 'coop.symbiotic.translationhelper']),
    ];

    $output .= '<thread><tr><th>' . implode('</th><th>', $headers) . '</th></tr></thead>';
    $output .= '<tbody>';

    foreach ($result['values'] as $key => $val) {
      $t = [
        $val->completed,
        $val->reviewed,
        $val->untranslated_entities,
        $val->untranslated_entities + $val->translated_entities,
        $val->last_update,
        $val->last_committer,
      ];

      $output .= '<tr><td>' . $key . '</td><td>' . implode('</td><td>', $t) . '</td><tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';

    // This shouldn't be necessary?
    if (CRM_Utils_Array::value('snippet', $_REQUEST) == 'json') {
      $foo = array('content' => $output);
      echo json_encode($foo);

      CRM_Utils_System::civiExit();
    }

    echo $output;
  }

}
