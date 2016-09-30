<?php

require_once 'translationhelper.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function translationhelper_civicrm_config(&$config) {
  _translationhelper_civix_civicrm_config($config);

  $config = CRM_Core_Config::singleton();
  $config->customTranslateFunction = 'translationhelper_ts';

  if (empty($_REQUEST['snipppet'])) {
    CRM_Core_Resources::singleton()->addScriptFile('coop.symbiotic.translationhelper', 'translationhelper.js');
    CRM_Core_Resources::singleton()->addStyleFile('coop.symbiotic.translationhelper', 'translationhelper.css');
  }
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function translationhelper_civicrm_xmlMenu(&$files) {
  _translationhelper_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function translationhelper_civicrm_install() {
  _translationhelper_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function translationhelper_civicrm_uninstall() {
  _translationhelper_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function translationhelper_civicrm_enable() {
  _translationhelper_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function translationhelper_civicrm_disable() {
  _translationhelper_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function translationhelper_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _translationhelper_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function translationhelper_civicrm_managed(&$entities) {
  _translationhelper_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 */
function translationhelper_civicrm_alterSettingsFolders(&$metaDataFolders) {
  _translationhelper_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * @param $angularModule
 */
function translationhelper_civicrm_angularModules(&$angularModules) {
  _translationhelper_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_navigationMenu.
 */
function translationhelper_civicrm_navigationMenu(&$menu) {
  $item1 = array(
    'name' => "Translation Helper Browser",
    'url' => 'civicrm/a/#/i18n',
    'permission' => 'translate CiviCRM',
  );

  $item2 = array(
    'name' => "Translation Helper Import",
    'url' => 'civicrm/translation/import?reset=1',
    'permission' => 'translate CiviCRM',
  );

  _translationhelper_civix_insert_navigation_menu($menu, 'Administer/Localization', $item1);
  _translationhelper_civix_insert_navigation_menu($menu, 'Administer/Localization', $item2);
}

/**
 *
 */
function translationhelper_ts($string, $params = array()) {
  // Copied from CRM_Core_I18n::ts()
  static $config = NULL;
  static $locale = NULL;
  static $i18n = NULL;

  $tsLocale = CRM_Core_I18n::getLocale();
  if (!$i18n or $locale != $tsLocale) {
    $i18n = CRM_Core_I18n::singleton();
    $locale = $tsLocale;
  }

  $keys = [
    $string,
    CRM_Utils_Array::value('domain', $params, ''),
  ];

  $hash = md5(implode(':', $keys));

  $translated = $i18n->crm_translate($string, $params);

  if (CRM_Utils_Array::value('escape', $params) == 'js') {
    // $translated = "<span class=\'translationhelper-string\' data-translationhelper-hash=\'$hash\'>$translated</span>";
    return $translated;
  }
  elseif ($string == 'Contacts' || $string == 'Go' || $string == 'Save' || $string == 'Cancel') {
    // FIXME: in templates/CRM/common/navigation.js.tpl
    // placeholder="{ts}Contacts{/ts}" should have escape=js?
    // Same for: value="{ts}Go{/ts}"
    return $translated;
  }
  else {
    $context = CRM_Utils_Array::value('context', $params, '');

    $translated = "<span class='translationhelper-string' data-translationhelper-key='$string' data-translationhelper-context='$context'>$translated</span>";
  }

  return $translated;
}
