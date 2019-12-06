<?php

use BabDev\Transifex\Transifex;

/**
 * Transifex.status API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_transifex_status($params) {
  $result = array(
    'values' => array(),
  );

  $resources = civicrm_api3('Transifex', 'getresource');

  require_once 'vendor/autoload.php';

  $transifex = new Transifex([
    'api.username' => civicrm_api3('Setting', 'getvalue', ['name' => 'translationhelper_transifex_login']),
    'api.password' => civicrm_api3('Setting', 'getvalue', ['name' => 'translationhelper_transifex_password']),
  ]);

  $lang = civicrm_api3('Setting', 'getvalue', ['name' => 'translationhelper_transifex_language']);

  foreach ($resources['values'] as $key => $val) {
    $cache = CRM_Core_BAO_Cache::getItem('transifex', 'transifex stats ' . $val->slug . ' ' . $lang);

    if ($cache === NULL) {
      $cache = $transifex->get('statistics')->getStatistics('civicrm', $val->slug, $lang);
      CRM_Core_BAO_Cache::setItem($cache, 'transifex', 'transifex stats ' . $val->slug . ' ' . $lang);
    }

    $result['values'][$val->slug] = $cache;
  }

  return $result;
}
