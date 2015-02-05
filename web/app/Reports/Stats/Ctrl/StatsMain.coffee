define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope', '$stateParams', '$q', '$timeout', 'DataService', 'Api',
  ($scope, $stateParams, $q, $timeout, DataService, Api) ->
    $scope.loaded = false

    customData = DataService.get('ReportBuilderCustom')
    builtInData = DataService.get('ReportBuilderBuiltIn')

    ####################################################################################################################
    # LOADING
    ####################################################################################################################

    promises = []

    promises.push customData.loadList().then( (list) -> $scope.custom_data_list = list)
    promises.push builtInData.loadList().then( (list) -> $scope.built_in_data_list = list)
    promises.push Api.sendGet('/reports/builder/group-params').then( (data) -> $scope.group_params = data.data)

    $q.all(promises).then(->
      # small delay gives chance for select2 boxes to set up, reduces visual jitter
      $timeout(->
        $scope.loaded = true
      , 350)
    )
  ]