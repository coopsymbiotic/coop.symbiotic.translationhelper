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

}
