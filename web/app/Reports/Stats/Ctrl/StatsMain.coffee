define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope', '$stateParams', '$q', '$timeout', 'DataService', 'Api', 'DashboardWidgetService',
  ($scope, $stateParams, $q, $timeout, DataService, Api, DashboardWidgetService) ->
    $scope.loaded = false

    customData = DataService.get('ReportBuilderCustom')
    builtInData = DataService.get('ReportBuilderBuiltIn')

    ####################################################################################################################
    # LOADING
    ####################################################################################################################

    promises = []
    $scope.labels = []

    promises.push customData.loadList().then( (list) -> $scope.custom_data_list = list )
    promises.push builtInData.loadList().then( (list) -> $scope.built_in_data_list = list )
    promises.push Api.sendGet('/reports/builder/group-params').then( (data) -> $scope.group_params = data.data)
    promises.push DashboardWidgetService.getReports().then (result) ->
      $scope.labels.push {title: label, active: false} for label in result.labels

    $q.all(promises).then(->
      # small delay gives chance for select2 boxes to set up, reduces visual jitter
      $timeout(->
        $scope.loaded = true
      , 350)
    )


    $scope.isActiveLabel = (label) ->
      index = -1
      index = Arrays.findIndex $scope.labels,
        (v) ->
          return true if v.title == label and v.active == true
      if index >= 0
        return true
      else
        return false
  ]