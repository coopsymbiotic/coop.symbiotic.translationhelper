<?php

class CRM_TranslationHelper_Utils {
  public static function getStringResourceFromCache($key, $context, $language) {
    // The DB has an index on the hash, not on the string_key.
    $hash = md5($key . ':' . $context);

    $dao = CRM_Core_DAO::executeQuery('SELECT resource FROM civicrm_translationhelper_cache WHERE string_hash = %1 AND language = %2', array(
      1 => [$hash, 'String'],
      2 => [$language, 'String'],
    ));

    if ($dao->fetch()) {
      return $dao->resource;
    }

    return NULL;
  }

  public static function getStringTranslationFromCache($key, $context, $language) {
    // The DB has an index on the hash, not on the string_key.
    $hash = md5($key . ':' . $context);

    $dao = CRM_Core_DAO::executeQuery('SELECT translation FROM civicrm_translationhelper_cache WHERE string_hash = %1 AND language = %2', array(
      1 => [$hash, 'String'],
      2 => [$language, 'String'],
    ));

    if ($dao->fetch()) {
      return $dao->translation;
    }

    return NULL;
  }

}
