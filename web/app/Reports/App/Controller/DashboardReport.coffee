define -> [
  '$scope', '$state', '$stateParams', 'DashboardService',
  ($scope,   $state,   $stateParams,   DashboardService) ->
    report_id = parseInt($stateParams.report_id)
    DashboardService.getReportById(report_id).then((loadedReport) ->
      $scope.currentReport = loadedReport
    )
]