define(function () {
  return ['$scope', 'Api', '$q', '$modal', function ($scope, Api, $q, $modal) {

    $scope.enableCustomFooter();

    $scope.pack.settings_def = $scope.pack.settings_def || [];
    $scope.definitions = {}
    for (var i = 0; i < $scope.pack.settings_def.length; i++) {
      var def = $scope.pack.settings_def[i];
      $scope.definitions[def.name] = def;

      if (def.default && !$scope.setting_values[def.name]) {
        $scope.setting_values[def.name] = def.default;
      }
    }

    var updateMeta = function () {
      var data = $scope.meta_defaults || {};
      $scope.loading_meta = true;
      $scope.meta_errors = null;
      $scope.meta_defaults = {};
      $scope.Ctrl.startSpinner('saving_settings');

      return Api.sendPostJson('/apps/packages/deskpro_jira/get-meta', data).then(
        function (res) {
          $scope.loading_meta = false;
          $scope.Ctrl.stopSpinner('saving_settings');

          if (res.data.errors) {
            $scope.meta_errors = res.data.errors;
          } else {
            $scope.meta = res.data;

            $scope.meta_defaults = {
              default_fields_list: $scope.meta.default_fields_list,
              default_fields_summary: $scope.meta.default_fields_summary,
              default_project: $scope.meta.default_project,
              default_issuetype: $scope.meta.default_issuetype
            };
          }
        },
        function () {
          $scope.loading_meta = false;
          $scope.Ctrl.stopSpinner('saving_settings');
        }
      );
    };

    $scope.getAccessToken = function () {
      var backUrl = window.location.href;
      window.location.href = '/admin/jira/request_token?back_url=' + encodeURIComponent(backUrl);
    };

    $scope.setPresaveCallback(function () {
      var deferred = $q.defer();

      //sanitize url
      var a = document.createElement('a');
      a.href = $scope.setting_values.url;
      $scope.setting_values.url = a.href;

      deferred.resolve();

      return deferred.promise;
    });

    $scope.saveSettings = function () {
      $scope.meta_errors = null;
      $scope.Ctrl.saveSettings().then(
        function () {
          updateMeta();
        },
        function () {
          updateMeta();
        }
      );
    };

    $scope.toggleField = function (field, isSummary) {
      var arr = $scope.meta_defaults['default_fields_' + (isSummary ? 'summary' : 'list')];
      var idx = arr.indexOf(field.id);
      idx > -1
        ? arr.splice(idx, 1)
        : arr.push(field.id);
    };

    updateMeta();
  }];
});