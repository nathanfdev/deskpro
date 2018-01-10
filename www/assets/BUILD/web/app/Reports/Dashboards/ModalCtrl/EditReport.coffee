define -> [
  '$scope',
  'DashboardService',
  '$modalInstance',
  'report',
  ($scope,
   DashboardService
   $modalInstance
   report
  ) ->

    $scope.activeTab = 'general'

    $scope.report = report
    $scope.schedule = {
      frequency: 'daily'
      when:
        time: '10:00'
        weekday: 'monday'
        monthday: 1
        monthday2: 15
      sendTo: ''
    }
    $scope.month = []

    for num in [1..31] by 1
      suffix = if num in [11, 12, 13] then 'th' else switch (num % 10)
        when 1 then 'st'
        when 2 then 'nd'
        when 3 then 'rd'
        else 'th'
      $scope.month.push({name: "#{num}#{suffix}", value: num})
    $scope.month.push({name: 'last day of month', value: 'last'})

    console.log $scope.month

    $scope.cancel = ->
      $modalInstance.dismiss('cancel')

    $scope.saveReport = ->
      $modalInstance.close($scope.report)

    $scope.scheduleReport= ->
      DashboardService.scheduleReport(report, $scope.schedule)
      $modalInstance.close($scope.report)
]
