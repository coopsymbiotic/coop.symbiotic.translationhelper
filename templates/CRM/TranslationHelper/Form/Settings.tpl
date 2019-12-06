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
        {capture assign="localizationURL"}href="{crmURL p='civicrm/admin/setting/localization' q='reset=1'}"{/capture}
        <div class="description">{ts 1=$localizationURL domain='coop.symbiotic.translationhelper'}The language must be enabled in the <a %1>localization settings</a>.{/ts}</div>
      </td>
    </tr>
  </table>

  {if $translationhelper_cache_update}
    <p>{ts 1=$translationhelper_cache_count 2=$translationhelper_cache_update}The local cache has %1 entries and was last updated on %2.{/ts} <a href="#" id="translationhelper-refresh-cache">{ts}Update the cache{/ts}</a></p>
  {else}
    <p>{ts 1=$translationhelper_cache_count}The local cache has %1 entries.{/ts} <a href="#" id="translationhelper-refresh-cache">{ts}Update the cache{/ts}</a></p>
  {/if}

  <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl" location="bottom"}</div>

  {literal}
    <script>
    /**
     * Settings "refresh cache".
     */
    CRM.$(function($) {
      $('#translationhelper-refresh-cache').click(function(event) {
        event.stopPropagation();
        CRM.alert('{/literal}{ts escape="js"}This might take a minute or two. CiviCRM core has more than 17,000 strings.{/ts}{literal}', '{/literal}{ts escape="js"}Refreshing...{/ts}{literal}', 'crm-msg-loading', {expires: 0});

        var id = window.setInterval(function() {
          CRM.api3('Transifex', 'Countcacheitems')
            .done(function(result) {
              $('#crm-notification-container .crm-msg-loading').append('<div>' + ts('Downloaded %1 strings', {1: result.result}) + '</div>');
            });
        }, 5000);

        CRM.api3('Transifex', 'Updatecache')
          .done(function(result) {
            window.clearInterval(id);

            if (result.is_error) {
              CRM.alert(result.error_message, '{/literal}{ts escape="js"}Refresh Error{/ts}{literal}', 'error');
            }
            else {
              CRM.alert('', '{/literal}{ts escape="js"}Ready{/ts}{/literal}', 'success');
              $('#crm-notification-container .crm-msg-loading').hide();
            }
          });

        return false;
      });
    });
    </script>
  {/literal}
</div>
