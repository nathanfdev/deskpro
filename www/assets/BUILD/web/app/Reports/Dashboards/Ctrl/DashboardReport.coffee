define ['DeskPRO/Util/Arrays'], (Arrays) -> [
  '$scope',
  '$state',
  '$stateParams',
  '$q',
  '$http',
  '$modal',
  'DashboardsInfo',
  'DashboardWidgetService',
  'DashboardService',
  ($scope,
   $state,
   $stateParams
   $q,
   $http,
   $modal,
   DashboardsInfo,
   DashboardWidgetService,
   DashboardService,
  ) ->
    $scope.loaded = false
    $scope.report = {
      dashboard: 0
      options: {}
      variables: []
    }
    $scope.widgets = []
    $scope.groupParams = DashboardWidgetService.groupParams

    report_id = parseInt($stateParams.report_id)
    $scope.report_id = parseInt($stateParams.report_id)

    $scope.gridsterOptions =
      margins: [13, 13],
      width: 10000,
      columns: 150,
      colWidth: 50,
      pushing: false,
      floating: false,
      swapping: true,
      draggable:
        enabled: false
        handle: '.box-header'
        stop: (event, $element, $widget) ->
          DashboardWidgetService.saveWidget($widget)
      resizable:
        enabled: false
        handles: ['n', 'e', 's', 'w', 'se', 'sw']
        stop: (event, $element, $widget) ->
          DashboardWidgetService.saveWidget($widget)

    load_promises = []
    load_promises.push DashboardsInfo.getReportDetail(report_id).then((loadedReport) ->
      $scope.report = loadedReport
      $scope.report.variables = loadedReport.variables

      DashboardsInfo.getDashboardDetail(loadedReport.dashboard).then( (db) ->
        $scope.dashboard = db
      )
    )

    load_promises.push DashboardWidgetService.getWidgets(report_id).then((widgets) ->
      $scope.widgets = widgets
    )

    $scope.me = {}
    load_promises.push DashboardsInfo.getMe().then( (me) ->
      $scope.me = me
    )

    $scope.agents = []
    load_promises.push DashboardsInfo.getAgents().then( (agents) ->
      agents.map((agent) => $scope.agents[agent.id] = agent)
    )

    $q.all(load_promises).then(-> $scope.updateReportVariables(() => $scope.loaded = true))

    # just reload info when its been changed
    $scope.$watch('dashboard.version_id + \'.\' + dashboard.reports_version_id', (n, o) ->
      return if not o
      reloadPromises = []
      if $scope.report.dashboard
        reloadPromises.push DashboardsInfo.getDashboardDetail($scope.report.dashboard).then( (db) ->
          $scope.dashboard = db
        )

      reloadPromises.push DashboardsInfo.getReportDetail(report_id).then((loadedReport) ->
        $scope.report = loadedReport
        $scope.report.variables = loadedReport.variables
        $scope.updateReportVariables()
      )

      $q.all(reloadPromises).then(->
        # all ok
        return
      , ->
        $state.go('reports.dashboards.view.empty')
      )
    )

    ####################################################################################################################
    # UI handlers
    ####################################################################################################################

    $scope.toggleLayoutEdit = () ->
      $scope.layoutEditing = !$scope.layoutEditing
      $scope.gridsterOptions.pushing = $scope.layoutEditing
      $scope.gridsterOptions.floating = $scope.layoutEditing
      $scope.gridsterOptions.draggable.enabled = $scope.layoutEditing
      $scope.gridsterOptions.resizable.enabled = $scope.layoutEditing


    ###
    # Staff for removing widget from dashboard. Works if and only if the dashboard.layoutEditing is switched on
    ###
    $scope.removeWidget = (widget) ->
      if $scope.layoutEditing
        index = DashboardWidgetService.getIndexById $scope.widgets, widget.id
        DashboardWidgetService
        .removeWidget(widget)
        .then () ->
          $scope.widgets.splice(index, 1)
          DashboardsInfo.getReportDetail($scope.report.id, true).then((loadedReport) ->
            $scope.report.variables = loadedReport.variables
            $scope.updateReportVariables()
          )

    $scope.download = (widget) ->
      window.open($http.formatApi2Url('/dashboard_report_widgets/' + widget.id + '/download/csv'))
      return true

    ####################################################################################################################
    # MODAL HANDLERS
    ####################################################################################################################

    $scope.openWidgetChoose = (report, widget, reportWidget) ->
      return if $scope.dashboard.is_default
      modalInstance = $modal.open({
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:widget-type-choose.html',
        controller: 'Reports.Dashboards.Modals.ChooseWidget'
        resolve:
          report:       -> report
          widget:       -> widget
          reportWidget: -> reportWidget
      })

      modalInstance.result.then (result) ->
        if result?.back == true
          $scope.openAddWidget(widget)
        if result?.add == true
          DashboardsInfo.getReportDetail($scope.report.id, true).then((loadedReport) ->
            $scope.report.variables = loadedReport.variables
            $scope.updateReportVariables()
          )
          DashboardWidgetService.getWidgets(report_id).then((widgets) ->
            $scope.widgets = widgets
            $scope.updateReportVariables()
          )

    $scope.openAddWidget = (widget) ->
      modalInstance = $modal.open {
        templateUrl: 'ReportsInterfaceBundle:Dashboard/Modal:add-widget.html',
        controller: 'Reports.Dashboards.Modals.AddWidget'
        resolve:
          report_id: -> report_id
          widget: -> widget
      }

      modalInstance.result.then (result) ->
        $scope.openWidgetChoose result.report, result.widget, result.reportWidget

    $scope.openEditWidget = (widget) ->
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:edit-widget.html",
        controller: 'Reports.Dashboards.Modals.EditWidget'
        resolve:
          widget: () ->
            widget
      }
      modalInstance.result.then (result) ->
        index = DashboardWidgetService.getIndexById $scope.widgets, widget.id
        $scope.widgets[index] = result
        DashboardWidgetService.saveWidget(result).then () ->


    $scope.editReportModal = (report) ->
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Dashboard/Modal:edit-report.html",
        controller: 'Reports.Dashboards.Modals.EditReport'
        resolve:
          report: () ->
            report
      }
      modalInstance.result.then (result) ->
        DashboardService.saveReport(result, true).then (savedReport) ->
          $scope.report = savedReport
          $state.go('reports.dashboards.view.report', { report_id: savedReport.id})


    $scope.changeReportLevelVar = () ->
      $scope.loaded = false

      DashboardService.saveReportVars($scope.report).then( () ->
        reloadPromises = []
        $scope.widgets = [];
        reloadPromises.push DashboardsInfo.getReportDetail($scope.report.id, true).then((loadedReport) ->
          $scope.report = loadedReport
        )

        reloadPromises.push DashboardWidgetService.getWidgets(report_id).then((widgets) ->
            $scope.widgets = widgets
          )

        $q.all(reloadPromises).then(->
          $scope.updateReportVariables(() => $scope.loaded = true)
        )
      )

    $scope.canViewAllAgents = () ->
      permission = $scope.dashboard.permissions.filter((permission) => permission.person == parseInt(window.DP_PERSON_ID))[0]
      return permission and permission.view_all

    $scope.updateReportVariables = (cb) ->



      if !$scope.report || !$scope.widgets.length || !$scope.dashboard || !$scope.me
        return

      vars = []
      $scope.widgets.map((widget) ->
        (widget.widget_variables || []).map((variable) ->
          if vars.map((reportVar) => reportVar.name).indexOf(variable.name) != -1
            return

          if variable.value == 'from_report_value'
            cloneVar = $.extend({}, variable);
            cloneVar.value = ''
            angular.forEach($scope.report.variables, (reportVar) ->
              if reportVar.name == cloneVar.name
                cloneVar.value = reportVar.value
            )

            vars.push(cloneVar)
          else if (variable.type == 'values' and (variable.field_type == 'agent' or variable.field_type == 'agent_team')) and $scope.dashboard.is_agent
            cloneVar = $.extend({}, variable);
            if variable.field_type == 'agent'
              cloneVar.value = parseInt($scope.me.person.id)
            else if variable.field_type == 'agent_team'
              cloneVar.value = parseInt($scope.me.person.primary_team)

            vars.push(cloneVar)
        )
      )

      $scope.report.variables = vars

      if cb then cb()
  ]
