(function(angular, $, _) {
  angular.module('translationhelper').config(function($routeProvider) {
      $routeProvider.when('/i18n', {
        controller: 'TranslationhelperListStringsCtrl',
        templateUrl: '~/translationhelper/ListStringsCtrl.html',

        // If you need to look up data when opening the page, list it out under "resolve".
        resolve: {
          enabled_languages: function(crmApi) {
            return crmApi('TranslationHelper', 'languages');
          }
        }
      });
    }
  );

  // The controller uses *injection*. This default injects a few things:
  //   $scope -- This is the set of variables shared between JS and HTML.
  //   crmApi, crmStatus, crmUiHelp -- These are services provided by civicrm-core.
  //   myContact -- The current contact, defined above in config().
  angular.module('translationhelper').controller('TranslationhelperListStringsCtrl', function($scope, $filter, crmApi, crmStatus, crmUiHelp, enabled_languages) {
    // The ts() and hs() functions help load strings for this module.
    var ts = $scope.ts = CRM.ts('translationhelper');

    // See: templates/CRM/translationhelper/ListStringsCtrl.hlp
    var hs = $scope.hs = crmUiHelp({file: 'CRM/translationhelper/ListStringsCtrl'});

    // These things are used in the .html file
    $scope.enabled_languages = enabled_languages.values;
    $scope.crmExportBaseUrl = CRM.url('civicrm/translation/export');
    $scope.crmExportUrl = '';

    // FIXME: we should centralise these things.
    // CRM_TranslationHelper_Page_Export also has a list of formats.
    $scope.availableExportFormats = {
      'ods' : 'ods',
      'excel2007' : 'xlsx'
    };

    $scope.format = 'ods';

    // Enable all languages by default.
    $scope.languages = {};

    for (var property in enabled_languages.values) {
      if (enabled_languages.values.hasOwnProperty(property)) {
        $scope.languages[property] = property;
      }
    }

    // Hide the download button by default
    $('#crm-i18n-download').hide();

    /**
     * Search callback.
     */
    $scope.search = function search() {
      var params = {};

      if (! $scope.entity) {
        return;
      }

      var selected_languages = [];

      for (var property in $scope.languages) {
        if ($scope.languages.hasOwnProperty(property) && $scope.languages[property] == property) {
          selected_languages.push(property);
        }
      }

      if (selected_languages.length <= 0) {
        CRM.alert('You must select at least one language.', '', 'warning');
        return;
      }

      params.language = selected_languages;

      if ($scope.entity) {
        params.entity = $scope.entity;
      }

      if ($scope.stringtext) {
        params.text = $scope.stringtext;
      }

      var columns = [
        { 'title': 'Entity Type', 'data': 'entity_type' },
        { 'title': 'Entity ID', 'data': 'entity_id' },
        { 'title': 'Field ID', 'data': 'field_id' },
        { 'title': 'Field Title', 'data': 'field_title' }
      ];

      var language_order = [];

      $(selected_languages).each(function(key, val) {
        columns.push({ 'title': val, 'data': 'value_' + val });
        language_order.push(val);
      });

      $("#crm-i18n-searchresults").addClass('blockOverlay');

      // Columns might change between reloads, so destroy the table completely.
      if ($.fn.dataTable.isDataTable('#crm-i18n-searchresults-table')) {
        $('#crm-i18n-searchresults-table').DataTable().destroy();
        $('#crm-i18n-searchresults-table').empty();
      }

      crmApi('TranslationString', 'get', params).then(function(result) {
        $('#crm-i18n-searchresults-table').DataTable({
          data: result.values,
          columns: columns,
          processing: true,
          fnDrawCallback: function(settings) {
            // FIXME Not very efficient way of enabling inline-edit on the table, but proof of concept.. one hopes.
            $(selected_languages).each(function(key, lang) {
              var child = 5 + key;

              $('#crm-i18n-searchresults-table td:nth-child(' + child + '):not(".crm-i18n-searchresults-processed")').each(function() {
                var entity_type = $(this).parent().find('td:first-child').text();
                var entity_id = $(this).parent().find('td:nth-child(2)').text();
                var field_id = $(this).parent().find('td:nth-child(3)').text();
                var language = language_order[key];

                var id = entity_type + '-' + entity_id + '-' + field_id;

                var html = $(this).html();
                $(this).html('<div class="crm-entity" data-entity="TranslationString" data-id="' + id + '"><div class="crm-editable" data-action="translate" data-type="text" data-field="' + language + '">' + _.escape(html) + '</div></div>');

                // Otherwise when paging back/forth in the results, we will process
                // the same elements over and over. Find a better way?
                $(this).closest('td').addClass('crm-i18n-searchresults-processed');
              });
            });

            $('#crm-i18n-searchresults-table').trigger('crmLoad');
          }
        });

        $('#crm-i18n-searchresults').removeClass('blockOverlay');

        // Assume there is something downloadable.
        $scope.crmExportUrl = $scope.crmExportBaseUrl + '?language=' + params.language.join(',') + '&entity=' + params.entity + '&format=' + $scope.format;
        $('#crm-i18n-download').show();
      });
    };

    $scope.updateExportFormat = function updateExportFormat() {
      // todo: store 'params' so we can do this in a cleaner way?
      $scope.crmExportUrl = $scope.crmExportUrl.replace(/format=\w+/, 'format=' + $scope.format);
    };
  });

})(angular, CRM.$, CRM._);
