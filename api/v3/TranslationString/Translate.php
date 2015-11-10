<?php

/**
 * TranslationString.translate API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_translation_string_translate($params) {
  // id = ContributionPage-1-title
  // en_us = Donate test

  list($entity_type, $entity_id, $field_id) = explode('-', $params['id']);

  $languages = CRM_TranslationHelper_BAO_FindStrings::enabledLanguages();

  foreach ($languages as $lang => $val) {
    if (isset($params[$lang])) {
      civicrm_api3($entity_type, 'setvalue', array(
        'id' => $entity_id,
        'field' => $field_id,
        'value' => $params[$lang],
      ));
    }
  }

  return civicrm_api3_create_success(1, $params, NULL, 'translate');
}
