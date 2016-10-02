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
    $('.translationhelper-string').click(function(event) {
      var string_key = $(this).data('translationhelper-key');
      var string_context = $(this).data('translationhelper-context');

      if ($('#translationhelper-panel-selectstring').data('translationhelper-selectstring') != 'enabled') {
        return;
      }

      $('#translationhelper-popup').dialog({
        title: ts('Translate String'),
        width: 1000,
        height: 600,
        modal: true,
        open: function() {
          $('#translationhelper-popup').html('<div class="crm-container"><div class="crm-loading-element"></div></div>');

          CRM.api3('Transifex', 'Gettranslation', {key: string_key, context: string_context})
            .done(function(result) {
              var html = '';

              $.each(result.values, function(index, value) {
                // todo: value.key
                html += '<p><strong>' + value.resource_slug + (value.context ? ' [' + value.context + '] ' : '') + '</strong>, '
                  + '<br>' + value.source_string
                  + '<br>' + value.translation
                  + '<br>(' + value.pluralized + ', ' + value.reviewed + ', ' + value.comment + ')</p>';
              });

              $('#translationhelper-popup').html(html);
            });
        },
        close: function() {
          $('#translationhelper-popup').html('');
        }
      });

      event.preventDefault();
    });

    /**
     * Panel button "Translate String".
     */
    $('#translationhelper-panel-selectstring').click(function(event) {
      if ($(this).data('translationhelper-selectstring') != 'enabled') {
        // Enable string select
        $(this).data('translationhelper-selectstring', 'enabled');
        $('body').addClass('translationhelper-selectstring-enabled');
        // FIXME: move to CSS
        $('#translationhelper-panel-selectstring').css('background', '#00406e');
      }
      else {
        $(this).data('translationhelper-selectstring', 'disabled');
        $('body').removeClass('translationhelper-selectstring-enabled');
        // FIXME: move to CSS
        $('#translationhelper-panel-selectstring').css('background', '#0064ab');
      }
    });
  });
})(CRM.$, CRM._, CRM.ts('translationhelper'));
