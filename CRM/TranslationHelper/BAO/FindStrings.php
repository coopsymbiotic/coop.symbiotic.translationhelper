<?php

/**
 * Helper classes to find strings. Mostly called by the API.
 */

class CRM_TranslationHelper_BAO_FindStrings {
  /**
   * Returns a list of allowed entities that can be used in the search.
   * (we don't want to allow, for example, a Contribution.get)
   *
   * @returns Array.
   */
  static function allowedEntities() {
    return array(
      'ContributionPage' => ts('Contribution Pages'),
      'Event' => ts('Event Pages'),
      'CustomGroup' => ts('Custom Groups'),
      'CustomField' => ts('Custom Fields'),
      'OptionGroup' => ts('Option Groups'),
      'OptionValue' => ts('Option Values'),
    );
  }

  /**
   * Wrapper to return enabled languages.
   */
  static function enabledLanguages() {
    return CRM_Core_I18n::languages(TRUE);
  }

  /**
   * Find CiviCRM configurations that match the given $params.
   *
   * @returns Array of $results
   */
  static function find($params) {
    $results = array();

    $entities = self::allowedEntities();
    $languages = self::enabledLanguages();

    // Filter and validate user parameters.
    self::filterParameter('entity', $params, $entities);
    self::filterParameter('language', $params, $languages);

    foreach ($entities as $entity_key) {
      // Fetch the multilingual (text) fields for the entity.
      $fields = array();
 
      $apiresult = civicrm_api3($entity_key, 'getfields');

      foreach ($apiresult['values'] as $field_id => $field_val) {
        // FIXME: we assume that string fields are multilingual, but not all of them are.
        if ($field_val['type'] == CRM_Utils_Type::T_STRING) {
          $fields[] = array(
            'entity_type' => $entity_key,
            'field_id' => $field_id,
            'field_title' => $field_val['title'],
          );
        }
      }

      $items = array();

      foreach ($languages as $language_key) {
        $apiresult2 = civicrm_api3($entity_key, 'get', array(
          'option.limit' => 0,
          'option.language' => $language_key,
        ));

        foreach ($apiresult2['values'] as $k1 => $v1) {
          $id = $v1['id'];

          foreach ($fields as $fval) {
            $x = $id . '-' . $fval['field_id'];

            if (! isset($items[$x])) {
              $items[$x] = array(
                'entity_type' => $entity_key,
                'entity_id' => $v1['id'],
                'field_id' => $fval['field_id'],
                'field_title' => $fval['field_title'],
                'id' => $id,
              );
            }

            $items[$x]['value_' . $language_key] = $v1[$fval['field_id']];
          }
        }
      }

      foreach ($items as $key => $val) {
        $results[] = $val;
      }
    }

    return $results;
  }

  /**
   * Returns the intersection of $params[$key] and $all_options.
   * Useful for validating and filtering user selections.
   */
  static function filterParameter($key, $params, &$all_options) {
    if (isset($params[$key])) {
      $t = $params[$key];

      if (is_array($t)) {
        $x = array_values($t);
        $y = array_keys($all_options);

        $all_options = array_intersect($x, $y);
      }
      else {
        $x = array($t);
        $y = array_keys($all_options);

        $all_options = array_intersect($x, $y);
      }
    }
  }

}
