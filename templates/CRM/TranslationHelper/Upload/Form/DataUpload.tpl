{crmScope extensionKey='coop.symbiotic.translationhelper'}
  <div class="translationhelper-upload-form-wrapper crm-block crm-form-block">
    <div class="help">
      <p>{ts}Add a short help text here?{/ts}</p>
    </div>

    <div class="crm-section" id="translationhelper-upload-file-section">
      <div class="label">{$form.uploadFile.label}</div>
      <div class="content">
        {$form.uploadFile.html}
        <div class="description">{ts}Supported file formats: OpenDocument (ods) or Excel (xlsx).{/ts}</div>
      </div>
      <div class="clear"></div>
    </div>

    <div class="crm-section">
      <div class="label"></div>
      <div class="content">
        {if $active_queues_count}
          <p>{ts plural="There are currently %count active import tasks:" count=$active_queues_count}There is currently an active import task:{/ts}</p>
          <table>
            <thead>
              <th>{ts}File{/ts}</th><th>{ts}Active since{/ts}</th><th>{ts}Remaining items{/ts}</th><th>{ts}Actions{/ts}</th>
            </thead>
            <tbody>
              {foreach from=$active_queues item=q}
                <tr>
                  <td>{$q.queue_name}</td>
                  <td>{$q.submit_time}</td>
                  <td>{$q.items}</td>
                  <td>
                    {if $q.may_run}
                      <a href="{crmURL p='civicrm/queue/runner' q="reset=1&qrid=`$q.queue_name`"}" title="{ts escape="js"}Run{/ts}"><i class="fa fa-2x fa-play"></i></a>
                    {/if}
                    <a href="{crmURL p='civicrm/translation/import' q="reset=1&flush=`$q.queue_name`"}" title="{ts escape="js"}Cancel{/ts}" style="padding-left: 1em;"><i class="fa fa-2x fa-fire-extinguisher"></i></a>
                  </td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        {else}
          <p style="padding-top: 2em;"><i class="fa fa-cog"></i> {ts}The import queue is currently empty. If an import task is already running, it will be displayed here.{/ts}</p>
        {/if}
      </div>
      <div class="clear"></div>
    </div>

    <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>

  </div>
{/crmScope}
