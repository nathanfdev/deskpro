define(function () {
  return ['$scope', 'Api', '$q', '$modal', function ($scope, Api, $q, $modal) {

    $scope.pack.settings_def = $scope.pack.settings_def || [];
    $scope.definitions = {}
    for (var i = 0; i < $scope.pack.settings_def.length; i++) {
      var def = $scope.pack.settings_def[i];
      $scope.definitions[def.name] = def;

      if (def.default && !$scope.setting_values[def.name]) {
        $scope.setting_values[def.name] = def.default;
      }
    }

    $scope.setPresaveCallback(function(){
      return Api.sendPost('/apps/packages/custom_addressfield/selected', {field_id: $scope.setting_values.custom_field});
    });

    $scope.Ctrl.startSpinner('loadingFields');
    Api.sendGet('/apps/packages/custom_addressfield/get-fields').then(
      function (res) {
        $scope.definitions.custom_field.options = res.data.fields;
        var $els = $('<tag></tag>');

        [
          'apps/custom_addressfield/js/vendor/dawa_autocomplete.js',
          'apps/custom_addressfield/js/UserInterface/CustomAdds.js'
        ].forEach(function(el){
            $els.append($('<script></script>').attr('src', res.data.url + 'file.php/' + el));
          });

        $scope.assets = $els.html();

        $scope.Ctrl.stopSpinner('loadingFields');
      },
      function (res) {
        $scope.loadingFields = false;
        $scope.error = res.data.error;
        $scope.Ctrl.stopSpinner('loadingFields');
      }
    );
  }];
});