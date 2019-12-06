<?php

use BabDev\Transifex\Transifex;

/**
 * Transifex.updatecache API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_transifex_updatecache($params) {
  $result = array(
    'values' => array(),
  );

  $resources = civicrm_api3('Transifex', 'getresource');
  $lang = Civi::settings()->get('translationhelper_transifex_language');

  require 'vendor/autoload.php';

  $transifex = new Transifex([
    'api.username' => Civi::settings()->get('translationhelper_transifex_login'),
    'api.password' => Civi::settings()->get('translationhelper_transifex_password'),
  ]);

  CRM_Core_DAO::executeQuery('TRUNCATE civicrm_translationhelper_cache');

  foreach ($resources['values'] as $key => $val) {
    $slug = $val->slug;
    $strings = $transifex->get('translationstrings')->getStrings('civicrm', $slug, $lang);

    foreach ($strings as $key2 => $val2) {
      $hash = md5($val2->key . ':' . $val2->context);

      CRM_Core_DAO::executeQuery('INSERT INTO civicrm_translationhelper_cache (string_key, string_hash, context, resource, domain, language, translation)
        VALUES(%1, %2, %3, %4, %5, %6, %7)', array(
          1 => array($val2->key, 'String'),
          2 => array($hash, 'String'),
          3 => array($val2->context, 'String'),
          4 => array($slug, 'String'),
          5 => array('civicrm', 'String'), // FIXME: for extensions this will be the ext longname
          6 => array($lang, 'String'),
          7 => array($val2->translation, 'String'),
      ));
    }
  }

  // Same the timestamp to have an idea of when we last did the update.
  // This is displayed in the Settings form, where the user can force a refresh.
  Civi::settings()->set('translationhelper_cache_update', time());

  return $result;
}
