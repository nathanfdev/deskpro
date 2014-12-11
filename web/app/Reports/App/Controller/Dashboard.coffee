define -> ['$scope', '$timeout', '$modal', '$rootScope', ($scope, $timeout, $modal, $rootScope) ->
  console.log($scope)
]


###
class Reports_Dashboard_Ctrl_Dashboard
    @CTRL_ID   = 'Reports_Dashboard_Ctrl_Dashboard'
    @CTRL_AS   = 'DashboardCtrl'
    @DEPS      = ['$scope', '$timeout', '$modal', '$rootScope']

    init: ->
      @service = @DataService.get('DashboardService')
      @widgetService = @DataService.get('DashboardWidgetService')
      @service.setWidgetService(@widgetService)
      @$scope.$watch 'dashboard', (newVal, oldVal) =>
        if newVal != oldVal
          @$scope.radioModel = @$scope.dashboard.name
          @$scope.gridsterOptions = @$scope.dashboard.options
        else
          if @$scope.dashboard?
            @$scope.radioModel = @$scope.dashboard.name
            @$scope.gridsterOptions = @$scope.dashboard.options
          else
            @$scope.radioModel == ""

    initialLoad: ->
      @service.getDashboards().then (dbs) =>
        @$scope.dashboards = dbs

        if @$scope.dashboards.length > 0
          dashboard = @$scope.dashboards[0]
          @$scope.dashboard = dashboard
        else
          @$scope.gridsterOptions =
            margins: [20, 20],
            columns: 10,
            draggable:
              handle: 'h3'

    setDash: (dash) ->
      @$scope.dashboard = dash;

    changeDashboard: (newDb) ->
      newDb.options.floating = false
      if newDb.loaded is false
        @service
          .getDashboard newDb
          .then (db) =>
            @$scope.dashboard = db
      else
        @$scope.dashboard = newDb


    NewDashboardModal: () ->
      modalInstance = @$modal.open {
        templateUrl: @getTemplatePath('Dashboard/new_dashboard.html'),
        controller: "Reports_Dashboard_Ctrl_ModalInstance"
        resolve:
          db: () =>
            return @$scope.dashboard
        }

      modalInstance.result.then (dashboard) =>
        @service.saveDashboard(dashboard).then () =>
          @$scope.dashboards.push(dashboard);
          @changeDashboard Arrays.last @$scope.dashboards

    editDashboardModal: () ->
      modalInstance = @$modal.open {
      templateUrl: @getTemplatePath('Dashboard/edit_dashboard.html'),
      controller: "Reports_Dashboard_Ctrl_ModalInstance"
      resolve:
        db: () =>
          return @$scope.dashboard
      }

      modalInstance.result.then (dashboard) =>
        @service.updateDashboard(dashboard)

    deleteDashboard: (dashboard) ->
      @startSpinner 'deleting'
      @service.deleteDashboard dashboard
        .then () =>
          @stopSpinner 'deleting'
          @changeDashboard Arrays.last @$scope.dashboards

    removeWidget: (widget) ->
      @widgetService
        .removeWidget(widget)
      Arrays.findAndRemove \
        @$scope.dashboard.widgets
      , (v, i) ->
        if v.id is widget.id then true else false
      , 1

    NewWidgetModal: ->
      modalInstance = @$modal.open {
          templateUrl: @getTemplatePath('Dashboard/new_widget.html'),
          controller: "Reports_Dashboard_Ctrl_ModalInstance"
          resolve:
            db: () =>
              return @$scope.dashboard
      }

      modalInstance.result.then (widgetInfo) =>
        @addWidget(widgetInfo)

    addWidget: (widgetInfo) ->
      widget =
        name: widgetInfo.title,
        col: 0,
        row: 0,
        sizeY: widgetInfo.y,
        sizeX: widgetInfo.x,
      #              type: widgetInfo.wtype,
      #              data: wData.Data || wData
      @widgetService.addWidget(@$scope.dashboard.id, widget)

    Reports_Dashboard_Ctrl_Dashboard.EXPORT_CTRL()

###