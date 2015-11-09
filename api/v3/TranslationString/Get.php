<?php

/**
 * TranslationString.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_translation_string_get($params) {
  $result = array(
    'values' => array(),
  );

  $result['values'] += CRM_TranslationHelper_BAO_FindStrings::find($params);

  return $result;
}
