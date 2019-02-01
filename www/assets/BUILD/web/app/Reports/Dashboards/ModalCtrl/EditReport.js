define(() => [
  '$scope',
  'DashboardService',
  'DashboardsInfo',
  '$modalInstance',
  'report',
  function($scope,
   DashboardService,
   DashboardsInfo,
   $modalInstance,
   report
  ) {

    $scope.activeTab = 'general';

    $scope.report = report;
    if ($scope.report.schedule) {
      $scope.enabled = 1;
      $scope.schedule = angular.copy($scope.report.schedule);
      $scope.schedule.send_to = $scope.schedule.send_to.join(',');
    } else {
      $scope.enabled = 0;
      $scope.schedule = {
        frequency: 'daily',
        when: {
          time: '10:00',
          weekday: 'monday',
          monthday: 1,
          monthday2: 15
        },
        send_to: ''
      };
    }

    $scope.month = [];

    for (var num = 1; num <= 31; num++) {
      const suffix = (() => {
        if ([11, 12, 13].includes(num)) { return 'th'; } else { switch (num % 10) {
        case 1: return 'st';
        case 2: return 'nd';
        case 3: return 'rd';
        default: return 'th';
      }
    }
      })();
      $scope.month.push({name: `${num}${suffix}`, value: num});
    }
    $scope.month.push({name: 'last day of month', value: 'last'});

    $scope.cancel = () => $modalInstance.dismiss('cancel');

    $scope.saveReport = function() {
      DashboardsInfo.clearLastReportDetail();
      return $modalInstance.close($scope.report);
    };

    return $scope.scheduleReport= () =>
      DashboardService.scheduleReport(report, $scope.schedule, $scope.enabled)
      .then(function() {
        DashboardsInfo.clearLastReportDetail();
        return $modalInstance.dismiss('scheduled');
      })
    ;
  }
] );
