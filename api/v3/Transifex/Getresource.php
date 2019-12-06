<?php

use BabDev\Transifex\Transifex;

/**
 * Transifex.getresource API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_transifex_getresource($params) {
  $result = array(
    'values' => array(),
  );

  $cache = CRM_Core_BAO_Cache::getItem('transifex', 'transifex resources');

  if ($cache !== NULL) {
    $result['values'] = $cache;
    return $result;
  }

  require 'vendor/autoload.php';

  $transifex = new Transifex([
    'api.username' => Civi::settings()->get('translationhelper_transifex_login'),
    'api.password' => Civi::settings()->get('translationhelper_transifex_password'),
  ]);

  $result['values'] = $transifex->get('resources')->getResources('civicrm');
  CRM_Core_BAO_Cache::setItem($result['values'], 'transifex', 'transifex resources');

  return $result;
}
