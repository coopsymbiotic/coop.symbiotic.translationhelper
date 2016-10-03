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

  /**
   * If the string is cached, fetch its 'resource' and limit our
   * search to that specific resource. Otherwise, loop through all
   * resources and find the first match.
   */
  $resources = [];

  $tmp = CRM_TranslationHelper_Utils::getStringResourceFromCache($params['key'], $params['context']);

  if ($tmp) {
    $resources[] = $tmp;
  }
  else {
    $tmp = civicrm_api3('Transifex', 'getresource');

    foreach ($tmp['values'] as $key => $val) {
      $resources[] = $val->slug;
    }
  }

  require_once 'vendor/autoload.php';

  $transifex = new Transifex([
    'api.username' => Civi::settings()->get('translationhelper_transifex_login'),
    'api.password' => Civi::settings()->get('translationhelper_transifex_password'),
  ]);

  $options = [
    'key' => urlencode($params['key']),
  ];

  if (! empty($params['context'])) {
    // FIXME: This does not work.
    // Ex: drush cvapi Transifex.gettranslation key='Date Formats' context='menu'
    // $options['context'] = urlencode($params['context']);
  }

  foreach ($resources as $slug) {
    // For now, skipping these resources because it's extremely unlikely
    // to be translating them (can't access them through the UI for now).
    if (in_array($slug, ['countries', 'install', 'provinces'])) {
      continue;
    }

    $lang = Civi::settings()->get('translationhelper_transifex_language');
    $tmp = $transifex->get('translationstrings')->getStrings('civicrm', $slug, $lang, FALSE, $options);

    if (empty($tmp)) {
      continue;
    }

    foreach ($tmp as $t) {
      if ($t->key == $params['key'] && $t->context == CRM_Utils_Array::value('context', $params, '')) {
        $result['values'][] = array(
          'comment' => $t->comment,
          'context' => $t->context,
          'key' => $t->key,
          'reviewed' => $t->reviewed,
          'pluralized' => $t->pluralized,
          'source_string' => $t->source_string,
          'translation' => $t->translation,
          'resource_slug' => $slug,
        );

        return $result;
      }
    }
  }

  return $result;
}
