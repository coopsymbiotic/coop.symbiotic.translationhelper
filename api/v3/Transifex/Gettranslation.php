<?php

use BabDev\Transifex\Transifex;

/**
 * Transifex.gettranslation API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_transifex_gettranslation($params) {
  $result = array(
    'values' => array(),
  );

  $resources = civicrm_api3('Transifex', 'getresource');

  require_once 'vendor/autoload.php';

  $transifex = new Transifex([
    'api.username' => Civi::settings()->get('translationhelper_transifex_login'),
    'api.password' => Civi::settings()->get('translationhelper_transifex_password'),
  ]);

  $options = [
    'key' => urlencode($params['key']),
  ];

  if (! empty($params['context'])) {
    // FIXME: not working?
    // Ex: drush cvapi Transifex.gettranslation key='Date Formats' context='menu'
    // $options['context'] = $params['context'];
  }

  // TODO:
  // - stop on first exact match?
  // - start by searching common-components?
  foreach ($resources['values'] as $key => $val) {
    $resource_slug = $val->slug;
    $lang = Civi::settings()->get('translationhelper_transifex_language');

    $tmp = $transifex->get('translationstrings')->getStrings('civicrm', $resource_slug, $lang, FALSE, $options);

    if (! empty($tmp)) {
      foreach ($tmp as $t) {
        $result['values'][] = array(
          'comment' => $t->comment,
          'context' => $t->context,
          'key' => $t->key,
          'reviewed' => $t->reviewed,
          'pluralized' => $t->pluralized,
          'source_string' => $t->source_string,
          'translation' => $t->translation,
          'resource_slug' => $resource_slug,
        );
      }
    }
  }

  return $result;
}
