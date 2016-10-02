<?php

use BabDev\Transifex\Transifex;
use BabDev\Transifex\Http;

/**
 * Transifex.createtranslation API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_transifex_createtranslation($params) {
  $result = array(
    'values' => array(),
  );

  require_once 'vendor/autoload.php';

  $transifex = new Transifex([
    'api.username' => Civi::settings()->get('translationhelper_transifex_login'),
    'api.password' => Civi::settings()->get('translationhelper_transifex_password'),
  ]);

  $project = 'civicrm';
  $resource = $params['resource'];
  $hash = md5($params['key'] . ':' . $params['context']);
  $lang = civicrm_api3('Setting', 'getvalue', ['name' => 'translationhelper_transifex_language']);
  $path = '/project/' . $project . '/resource/' . $resource . '/translation/' . $lang . '/strings/';

  // FIXME: inform the user?
  if (empty($params['value'])) {
    $result['values'][] = "Empty translation";
    return $result;
  }

  $data = [
    [
      'source_entity_hash' => $hash,
      'translation' => $params['value'],
    ],
  ];

  $headers = $transifex->getOption('headers');
  $options = array('headers' => $headers);
  $http = new Http($options);

  $response = $http->put(
    $transifex->getOption('api.url') . $path,
    json_encode($data),
    array('Content-Type' => 'application/json')
  );

  if ($response->code != 200) {
    throw new Exception("Transifex update failed: " . $response->body);
  }

  return $result;
}
