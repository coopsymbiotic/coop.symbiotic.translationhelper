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

    $scope.enabled_languages = enabled_languages.values;

    // Enable all languages by default.
    $scope.languages = {};

    for (var property in enabled_languages.values) {
      if (enabled_languages.values.hasOwnProperty(property)) {
        $scope.languages[property] = property;
      }
    }

    /**
     * Search callback.
     */
    $scope.search = function search() {
      var params = {};

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

      $(selected_languages).each(function(key, val) {
        columns.push({ 'title': val, 'data': 'value_' + val });
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
          processing: true
        });

        $('#crm-i18n-searchresults').removeClass('blockOverlay');
      });
    };
  });

})(angular, CRM.$, CRM._);
