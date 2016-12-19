define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope', '$stateParams', '$q', '$timeout', 'DataService', 'Api', 'DashboardWidgetService',
  ($scope, $stateParams, $q, $timeout, DataService, Api, DashboardWidgetService) ->
    $scope.loaded = false

    customData = DataService.get('ReportBuilderCustom')
    builtInData = DataService.get('ReportBuilderBuiltIn')
    # TODO looks ugly cause this code duplicates (almost) code in AddWidget.coffee
    ####################################################################################################################
    # LOADING
    ####################################################################################################################

    promises = []
    $scope.labels = []
    $scope.selectedLabels = 0
    $scope.searchText = ''
    $scope.custom_data_list_prefiltered = []
    $scope.built_in_data_list_prefiltered = []

    promises.push customData.loadList().then( (list) -> $scope.custom_data_list = list )
    promises.push builtInData.loadList().then( (list) -> $scope.built_in_data_list = list )
    promises.push Api.sendGet('/reports/builder/group-params').then( (data) -> $scope.group_params = data.data)
    promises.push DashboardWidgetService.getReports().then (result) ->
      $scope.labels.push {title: label, active: false} for label in result.labels

    $q.all(promises).then(->
      # small delay gives chance for select2 boxes to set up, reduces visual jitter
      $timeout(->
        $scope.filterByLabels()
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

    ####################################################################################################################
    # Sort'n'filter
    ####################################################################################################################

    # Mmmm... super script to toggle labels by it's title or label itself
    $scope.toggleLabel = (label) ->
      if typeof label == 'string'
        index = -1
        index = Arrays.findIndex $scope.labels,
          (v) ->
            return true if v.title == label
        if index >= 0
          label = $scope.labels[index]
      if label?
        currentLabel = $scope.labels[$scope.labels.indexOf(label)]
        if currentLabel.active == true
          $scope.selectedLabels -= 1
        else
          $scope.selectedLabels += 1
        currentLabel.active = !currentLabel.active
        $scope.filterByLabels()

    ###
    # Filter prefilteredReports by labels
    ###
    $scope.filterByLabels = () ->
      if $scope.selectedLabels != 0
        $scope.custom_data_list_prefiltered = $scope.custom_data_list.filter (report) ->
          return true for label in report.labels when $scope.isActiveLabel(label)
        $scope.built_in_data_list_prefiltered = $scope.built_in_data_list.filter (report) ->
          return true for label in report.labels when $scope.isActiveLabel(label)
      else
        $scope.custom_data_list_prefiltered = $scope.custom_data_list
        $scope.built_in_data_list_prefiltered = $scope.built_in_data_list

    ###
    # Filter prefilteredReports by labels
    # Note that this will filter by label.title too (@see OneNote->DR->ImplementingDesign->(4) Adding Widgets)
    ###
    $scope.filterBySearchText = (value) ->
      if $scope.searchText == ''
        return true
      else
        search = $scope.searchText.toLocaleLowerCase()
        if value.title.toLocaleLowerCase().indexOf(search) >= 0
          return true
        else
          return true for label in value.labels when label.toLocaleLowerCase().indexOf(search) >= 0
          return false
  ]