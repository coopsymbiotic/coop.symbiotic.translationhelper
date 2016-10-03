<?php

class CRM_TranslationHelper_Utils {
  public static function getStringResourceFromCache($string_key, $context) {
    $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_translationhelper_cache WHERE string_key = %1 AND context = %2', array(
      1 => array($string_key, 'String'),
      2 => array($context, 'String'),
    ));

    if ($dao->fetch()) {
      return $dao->resource;
    }

    return NULL;
  }
}
