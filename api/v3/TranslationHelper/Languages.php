<?php

/**
 * TranslationHelper.languages API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_translation_helper_languages($params) {
  $result = array(
    'values' => array(),
  );

  $result['values'] += CRM_TranslationHelper_BAO_FindStrings::enabledLanguages();

  return $result;
}
