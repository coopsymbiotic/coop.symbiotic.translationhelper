(function($, _, ts){
  $(function() {
    if (typeof CRM.translationhelper == 'undefined') {
      CRM.translationhelper = {};
    }

    if (typeof CRM.translationhelper.initialized != 'undefined') {
      return;
    }

    CRM.translationhelper.initialized = true;

    $('body').append('<div id="translationhelper-panel">'
      + '<div id="translationhelper-panel-label"><a href="#">' + ts('Translate') + '</a></div>'
      + '<div id="translationhelper-panel-details">'
      + '  <a href="#" id="translationhelper-panel-selectstring"><i class="fa fa-mouse-pointer" aria-hidden="true"></i> ' + ts('Translate String') + '</a>'
      + '  <a href="' + CRM.url('civicrm/translation/settings', {reset:1}) + '" class="crm-popup"><i class="fa fa-wrench" aria-hidden="true"></i> ' + ts('Settings') + '</a>'
      + '  <a href="' + CRM.url('civicrm/translation/stats', {reset:1}) + '" class="crm-popup"><i class="fa fa-line-chart" aria-hidden="true"></i> ' + ts('Statistics') + '</a>'
      + '  <a href="https://chat.civicrm.org/civicrm/channels/i18n-l10n" target="_blank" title="' + ts("CiviCRM Mattermost chat, opens in a new window", {escape:'js'}) + '"><i class="fa fa-comments-o" aria-hidden="true"></i> ' + ts('Chat with us') + '</a>'
      + '</div>'
      + '</div>');

    $('body').append('<div id="translationhelper-popup"></div>');

    $('#translationhelper-panel-label > a').click(function(event) {
      if ($('#translationhelper-panel').width() <= 100) {
        $('#translationhelper-panel').animate({
          width: 280
        }, 700, function() {
          $('#translationhelper-panel-details').show();
          $('#translationhelper-panel').addClass('translationhelper-panel-open');
        });
      }
      else {
        $('#translationhelper-panel-details').hide();
        $('#translationhelper-panel').removeClass('translationhelper-panel-open');
        $('#translationhelper-panel').animate({width: 70}, 500);
      }

      event.preventDefault();
    });

    /**
     * Translatable string is clicked.
     */
    CRM.translationHelperTranslateString = function(event) {
      var string_key = $(this).data('translationhelper-key');
      var string_context = $(this).data('translationhelper-context');

      event.preventDefault();

      if ($('#translationhelper-panel-selectstring').data('translationhelper-selectstring') != 'enabled') {
        return;
      }

      $('#translationhelper-popup').dialog({
        title: ts('Translate String'),
        width: 1000,
        // height: 500,
        modal: true,
        open: function() {
          $('#translationhelper-popup').html('<div class="crm-container"><div class="crm-loading-element"></div></div>');

          CRM.api3('Transifex', 'Gettranslation', {key: string_key, context: string_context})
            .done(function(result) {
              var html = '<form>';

              $.each(result.values, function(index, value) {
                // NB: form-textarea class is from bootstrap, not civicrm.
                html += '<p><strong>' + value.resource_slug + (value.context ? ' [' + value.context + '] ' : '') + '</strong>, '
                  + '<div>' + value.source_string + '</div>'
                  + '<div><textarea rows="3" cols="80" class="crm-form-text form-textarea" style="width: 100%;" '
                  + '  data-translationhelper-resource="' + value.resource_slug + '"'
                  + '  data-translationhelper-key="' + value.key + '"'
                  + '  data-translationhelper-context="' + value.context + '">' + value.translation + '</textarea></div>'
                  + '<br>(' + (value.pluralized ? 'pluralized' : 'no plural') + ', ' + (value.reviewed ? 'reviewed' : 'not reviewed') + (value.comment ? ', ' + value.comment : '') + ')</p>';
              });

              html += '<input type="submit" class="crm-form-submit" value="' + ts('Submit', {escape:'js'}) + '">';
              html += '</form>';

              $('#translationhelper-popup').html(html);

              /**
               * Send translation to Transifex.
               */
              $('#translationhelper-popup input.crm-form-submit').click(function(event) {
                event.preventDefault();
                $(this).hide();
                $('#translationhelper-popup').append('<div class="crm-container"><div class="crm-loading-element"></div></div>');

                // If there aren't any translation fields, close the dialog now.
                if ($('#translationhelper-popup textarea').size() == 0) {
                  $('#translationhelper-popup').dialog('close');
                  CRM.translationHelperDisableTranslateString();
                  return;
                }

                var translations_found = false;

                // FIXME: need to decide if it will be possible to have multiple strings
                // to translate at once.. currently this closes the popup after processing
                // the first string. There aren't known use-cases for having multiple strings
                // at once.
                $('#translationhelper-popup textarea').each(function(index, value) {
                  // md5 in JS would require a third-party library.
                  var resource = $(this).data('translationhelper-resource');
                  var key = $(this).data('translationhelper-key');
                  var context = $(this).data('translationhelper-context');
                  var translation = $(this).val();

                  if (translation) {
                    translations_found = true;

                    CRM.api3('Transifex', 'Createtranslation', {resource: resource, key: key, context: context, value: translation})
                      .done(function(result) {
                        if (result.is_error) {
                          CRM.alert(result.error_message, ts("Error"), 'error');
                        }
                        else {
                          CRM.status(ts("Saved"));
                        }

                        $('#translationhelper-popup').dialog('close');
                        CRM.translationHelperDisableTranslateString();
                    });
                  }
                });

                if (!translations_found) {
                  $('#translationhelper-popup').dialog('close');
                  CRM.translationHelperDisableTranslateString();
                  CRM.status(ts("Cancelled"));
                }
              });
            });
        },
        close: function() {
          $('#translationhelper-popup').html('');
        }
      });
    };

    /**
     * Enables the clickable strings.
     *
     * NB: we enable/disable this callback so that it is easier to re-attach
     * the event to strings that have shown up on the screen after the initial load.
     */
    CRM.translationHelperEnableTranslateString = function() {
      $('body').addClass('translationhelper-selectstring-enabled');
      $('.translationhelper-string').on('click', CRM.translationHelperTranslateString);

      // FIXME: move to CSS
      $('#translationhelper-panel-selectstring').css('background', '#5cb85c');
    };

    /**
     * Disables the clickable strings.
     */
    CRM.translationHelperDisableTranslateString = function() {
      $('body').removeClass('translationhelper-selectstring-enabled');
      $('.translationhelper-string').off('click', CRM.translationHelperTranslateString);

      // FIXME: move to CSS
      $('#translationhelper-panel-selectstring').css('background', '#0064ab');
    };

    /**
     * Panel button "Translate String".
     */
    $('#translationhelper-panel-selectstring').click(function(event) {
      if ($(this).data('translationhelper-selectstring') != 'enabled') {
        // Enable string select
        $(this).data('translationhelper-selectstring', 'enabled');
        CRM.translationHelperEnableTranslateString();
      }
      else {
        $(this).data('translationhelper-selectstring', 'disabled');
        CRM.translationHelperDisableTranslateString();
      }
    });

    /**
     * Settings "refresh cache".
     */
    $('#translationhelper-refresh-cache').click(function(event) {
      event.stopPropagation();
      CRM.alert(ts('This might take a minute or two.'), ts('Refreshing...'), 'crm-msg-loading', {expires: 0});
      CRM.api3('Transifex', 'Updatecache', {}, {
        'callBack' : function(result){
          if (result.is_error) {
            CRM.alert(result.error_message, '{/literal}{ts escape="js"}Refresh Error{/ts}{literal}', 'error');
          } else {
            CRM.alert('', ts('Ready'), 'success');
          }
        }
      });
      return false;
    });
  });
})(CRM.$, CRM._, CRM.ts('translationhelper'));
