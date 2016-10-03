<?php

/**
 * Transifex.countcacheitems API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_transifex_countcacheitems($params) {
  $lang = Civi::settings()->get('translationhelper_transifex_language');
  $count = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_translationhelper_cache WHERE language = %1', array(
    1 => array($lang, 'String'),
  ));

  return $count;
}
