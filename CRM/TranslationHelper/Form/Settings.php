<?php

class CRM_TranslationHelper_Form_Settings extends CRM_Admin_Form_Setting {
  protected $_settingFilter = [
    'group' => 'translationhelper',
  ];

  protected $_settings = [
    'translationhelper_transifex_login' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'translationhelper_transifex_password' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
    'translationhelper_transifex_language' => CRM_Core_BAO_Setting::SYSTEM_PREFERENCES_NAME,
  ];

  public function buildQuickForm() {
    // Counter of cached items.
    $count = civicrm_api3('Transifex', 'countcacheitems');
    $this->assign('translationhelper_cache_count', $count);

    // Date of last update
    $update = Civi::settings()->get('translationhelper_cache_update');

    if ($update) {
      $update = date('Y-m-d h:i', $update);
      $update = CRM_Utils_Date::customFormat($update);
      $this->assign('translationhelper_cache_update', $update);
    }

    parent::buildQuickForm();
  }
}
