<div class="crm-block crm-form-block crm-reporterror-form-block">
  <h3>{ts domain='coop.symbiotic.translationhelper'}Transifex Configurations{/ts}</h3>

  <p>{ts domain='coop.symbiotic.translationhelper' 1="href='https://www.transifex.com/'" 2="href='https://www.transifex.com/civicrm/civicrm/dashboard/'"}To contribute to CiviCRM translations, you must have an account on <a %1>Transifex</a> and be a member of a <a %2>CiviCRM language team</a>.{/ts}</p>
  <p>{ts domain='coop.symbiotic.translationhelper' 1="href='https://wiki.civicrm.org/confluence/pages/viewpage.action?pageId=88408149'" 2="href='https://chat.civicrm.org/civicrm/channels/i18n-l10n'"}Please read the CiviCRM Resources for Translators</a> documentation before. Join us in the <a %2>CiviCRM translation chat</a> to exchange with other translators.{/ts}</p>

  <table class="form-layout-compressed">
    <tr class="crm-translationhelper-form-block">
      <td class="label">{$form.translationhelper_transifex_login.label}</td>
      <td>{$form.translationhelper_transifex_login.html}</td>
    </tr>
    <tr class="crm-translationhelper-form-block">
      <td class="label">{$form.translationhelper_transifex_password.label}</td>
      <td>{$form.translationhelper_transifex_password.html}</td>
    </tr>
    <tr class="crm-translationhelper-form-block">
      <td class="label">{$form.translationhelper_transifex_language.label}</td>
      <td>{$form.translationhelper_transifex_language.html}
        <div class="description">Ex: fr_CA, en_UK, jp_JP, etc. (TODO: this should be a select)</div>
      </td>
    </tr>
  </table>
  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>
</div>
