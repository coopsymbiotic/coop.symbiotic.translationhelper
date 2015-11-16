<div class="translationhelper-upload-form-wrapper crm-block crm-form-block">
  {if count($errors)}
    <div class="messages errors help">
      <p>{ts}The following errors were found. Please go back to upload a new file and try again.{/ts}</p>
    </div>

    <ul>
      {foreach from=$errors item=error}
        <li>{$error}</li>
      {/foreach}
    </ul>

  {else}
    <div class="messages help">
      <p>{ts}Please review the information below and select the languages you wish to import.{/ts}</p>
    </div>

    {foreach from=$elementNames item=f}
      <div class="crm-section">
        <div class="label">{$form.$f.label}</div>
        <div class="content">
          {$form.$f.html}
        </div>
        <div class="clear"></div>
      </div>
    {/foreach}
  {/if}

  {if count($warnings)}
    <div class="messages warnings help">
      <p>{ts}Warning:{/ts}</p>

      <ul>
        {foreach from=$warnings item=warning}
          <li>{$warning}</li>
        {/foreach}
      </ul>
    </div>
  {/if}

  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
