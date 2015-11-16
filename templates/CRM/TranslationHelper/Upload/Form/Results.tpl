<div class="devpdataviz-upload-form-wrapper crm-block crm-form-block">
  <h3>{ts}Import complete{/ts}</h3>

  <div class="messages help">
    <p>{ts}Import task is complete. <a href="{crmURL p='civicrm/translation/import' q='reset=1'}">Click here to import more files.</a>{/ts}</p>
  </div>

  <div class="crm-section">
    <div class="label">{ts}Items processed:{/ts}</div>
    <div class="content">{$import_success}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{ts}Errors:{/ts}</div>
    <div class="content">{$import_fails}</div>
    <div class="clear"></div>
  </div>

  {if count($import_error_messages)}
  <div class="crm-section">
    <div class="label"></div>
    <div class="content">
      {foreach from=$import_error_messages item=msg}
        <p>{$msg}</p>
      {/foreach}
    </div>
    <div class="clear"></div>
  </div>
  {/if}

  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
