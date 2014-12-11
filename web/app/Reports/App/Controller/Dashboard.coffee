define -> [
  '$scope', '$timeout', '$modal', '$rootScope', 'DashboardService', 'DashboardWidgetService',
  ($scope, $timeout, $modal, $rootScope, DashboardService, DashboardWidgetService) ->

    DashboardService.setWidgetService(DashboardWidgetService)

#    DashboardService.getDashboards().then (dbs) =>
#      $scope.dashboards = dbs
#
#      if $scope.dashboards.length > 0
#        dashboard = $scope.dashboards[0]
#        $scope.dashboard = dashboard
#      else
#        $scope.gridsterOptions =
#          margins: [20, 20],
#          columns: 10,
#          draggable:
#            handle: 'h3'


    $scope.$watch 'dashboard', (newVal, oldVal) =>
      if newVal != oldVal
        $scope.radioModel = $scope.dashboard.name
        $scope.gridsterOptions = $scope.dashboard.options
      else
        if $scope.dashboard?
          $scope.radioModel = $scope.dashboard.name
          $scope.gridsterOptions = $scope.dashboard.options
        else
          $scope.radioModel == ""

    $scope.setDash = (dash) ->
      $scope.dashboard = dash;

    $scope.changeDashboard = (newDb) ->
      newDb.options.floating = false
      if newDb.loaded is false
        DashboardService
        .getDashboard newDb
        .then (db) =>
          $scope.dashboard = db
      else
       @$scope.dashboard = newDb


    $scope.NewDashboardModal = () ->
      modalInstance = $modal.open {
#        templateUrl: @getTemplatePath('Dashboard/new_dashboard.html'),
        controller: "Reports.App.ModalInstance"
        resolve:
          db: () =>
            return $scope.dashboard
      }

      modalInstance.result.then (dashboard) =>
        DashboardService.saveDashboard(dashboard).then () =>
          $scope.dashboards.push(dashboard);
          $scope.changeDashboard Arrays.last $scope.dashboards

    $scope.editDashboardModal = () ->
      modalInstance = $modal.open {
#        templateUrl: @getTemplatePath('Dashboard/edit_dashboard.html'),
        controller: "Reports.App.ModalInstance"
        resolve:
          db: () =>
            return $scope.dashboard
      }

      modalInstance.result.then (dashboard) =>
        DashboardService.updateDashboard(dashboard)

    $scope.deleteDashboard = (dashboard) ->

      DashboardService.deleteDashboard dashboard
      .then () =>
        $scope.changeDashboard Arrays.last $scope.dashboards

    $scope.removeWidget = (widget) ->
      DashboardWidgetService
      .removeWidget(widget) \
        Arrays.findAndRemove \
          $scope.dashboard.widgets
        , (v, i) ->
          if v.id is widget.id then true else false
        , 1

    $scope.NewWidgetModal = ->
      modalInstance = $modal.open {
#        templateUrl: getTemplatePath('Dashboard/new_widget.html'),
        controller: "Reports.App.ModalInstance"
        resolve:
          db: () =>
            return $scope.dashboard
      }

      modalInstance.result.then (widgetInfo) =>
        $scope.addWidget(widgetInfo)

    $scope.addWidget = (widgetInfo) ->
      widget =
        name: widgetInfo.title,
        col: 0,
        row: 0,
        sizeY: widgetInfo.y,
        sizeX: widgetInfo.x,
]