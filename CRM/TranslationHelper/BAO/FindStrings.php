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
      'Group' => ts('Groups'),
      'CustomGroup' => ts('Custom Groups'),
      'CustomField' => ts('Custom Fields'),
      'OptionGroup' => ts('Option Groups'),
      'OptionValue' => ts('Option Values'),
      'UFGroup' => ts('Profile Groups'),
      'UFField' => ts('Profile Fields'),
    );
  }

  /**
   * Wrapper to return enabled languages.
   */
  static function enabledLanguages() {
    static $languages = NULL;

    if (empty($languages)) {
      $languages = CRM_Core_I18n::languages(TRUE);
    }

    return $languages;
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

    $supported_types = array(
      CRM_Utils_Type::T_STRING,
      CRM_Utils_Type::T_LONGTEXT,
      CRM_Utils_Type::T_BLOB,
      CRM_Utils_Type::T_URL,
    );

    // A few exceptions until we add 'is multilingual' to the civicrm schema/dao
    $option_group_fields = array(
      'label',
      'description',
    );

    $group_fields = array(
      'title',
      'description',
    );

    foreach ($entities as $entity_key) {
      // Fetch the multilingual (text) fields for the entity.
      $fields = array();
 
      $apiresult = civicrm_api3($entity_key, 'getfields');

      foreach ($apiresult['values'] as $field_id => $field_val) {
        // FIXME: we assume that string fields are multilingual, but not all of them are.
        // We check for the above supported_types, as well as making sure that it's not a 'select' field.
        if (in_array($field_val['type'], $supported_types) && (empty($field_val['html']) || $field_val['html']['type'] != 'Select')) {
          if ($field_val['name'] == 'name') {
            continue;
          }

          // A few exceptions until we add 'is multilingual' to the civicrm schema/dao
          if ($entity_key == 'OptionValue' && ! in_array($field_val['name'], $option_group_fields)) {
            continue;
          }

          if ($entity_key == 'Group' && ! in_array($field_val['name'], $group_fields)) {
            continue;
          }

          $fields[] = array(
            'entity_type' => $entity_key,
            'field_id' => $field_val['name'],
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
   *
   * Always returns the keys of an array, since that's what we usually use later on.
   */
  static function filterParameter($key, $params, &$all_options) {
    if (empty($params[$key])) {
      $all_options = array_keys($all_options);
      return;
    }

    $t = $params[$key];

    if (is_array($t)) {
      $x = array_values($t);
      $y = array_keys($all_options);

      $all_options = array_intersect($x, $y);
    }
    else {
      if ($t == 'all') {
        $all_options = array_keys($all_options);
        return;
      }

      $x = explode(',', $t);
      $y = array_keys($all_options);

      $all_options = array_intersect($x, $y);
    }
  }

}
