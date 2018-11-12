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
    $scope.layoutEditing = false

    report_id = parseInt($stateParams.report_id)
    $scope.report_id = parseInt($stateParams.report_id)
    $scope.autoRefresh = if localStorage.getItem("dp.dashboard.autoRefresh.#{$scope.report_id}") == '1' then 1 else 0
    if $scope.autoRefresh
      $scope.refreshInterval = setInterval(=>
        $scope.refreshDashboardReport()
      , 10*60*1000)

    $scope.gridsterOptions =
      margins: [13, 13],
      width: 10000,
      columns: 150,
      colWidth: 50,
      pushing: true,
      floating: true,
      swapping: true,
      draggable:
        enabled: false
        handle: '.box-header'
      resizable:
        enabled: false
        handles: ['n', 'e', 's', 'w', 'se', 'sw']

    load_promises = []
    load_promises.push DashboardsInfo.getReportDetail(report_id).then((loadedReport) ->
      $scope.report = loadedReport
      $scope.report.variables = loadedReport.variables
      $scope.had_empty_vars = !loadedReport.variables || loadedReport.variables.length == 0

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

    $q.all(load_promises).then(-> $scope.updateReportVariables(false, () => $scope.loaded = true))

    # just reload info when its been changed
    $scope.$watch(
      () -> DashboardsInfo.lastDashboardDetail,
      () ->
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
          if $scope.had_empty_vars
            $scope.had_empty_vars = false
            DashboardService.saveReportVars($scope.report, true).then( () ->
              $scope.refreshDashboardReport()
            )

          return
        , ->
          $state.go('reports.dashboards.view.empty')
        )
      , true
    )

    ####################################################################################################################
    # UI handlers
    ####################################################################################################################

    $scope.toggleLayoutEdit = () ->
      $scope.layoutEditing = !$scope.layoutEditing
#      $scope.gridsterOptions.pushing = $scope.layoutEditing
#      $scope.gridsterOptions.floating = $scope.layoutEditing
      $scope.gridsterOptions.draggable.enabled = $scope.layoutEditing
      $scope.gridsterOptions.resizable.enabled = $scope.layoutEditing


    ###
    # Staff for removing widget from dashboard. Works if and only if the dashboard.layoutEditing is switched on
    ###
    $scope.removeWidget = (widget) ->
      removeWidget = () ->
        index = DashboardWidgetService.getIndexById $scope.widgets, widget.id
        DashboardWidgetService
          .removeWidget(widget)
          .then () ->
            $scope.widgets.splice(index, 1)
            DashboardsInfo.getReportDetail($scope.report.id, true).then((loadedReport) ->
              $scope.report.variables = loadedReport.variables
              $scope.updateReportVariables(true)
            )

      if $scope.layoutEditing
        $modal.open({
          templateUrl: "ReportsInterfaceBundle:Index:modal-confirm.html",
          controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->

            $scope.title   = 'Confirm discard'
            $scope.message = 'Are you sure you want to delete this widget?'

            $scope.dismiss = ->
              $modalInstance.dismiss()

            $scope.confirm = ->
              removeWidget()
              $modalInstance.dismiss()
          ]
        })

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
        DashboardService.saveReport(result).then (savedReport) ->
          DashboardsInfo.getReportDetail(report_id).then((loadedReport) ->
            $scope.report = loadedReport
            $scope.report.variables = loadedReport.variables
            $state.go('reports.dashboards.view.report', { report_id: loadedReport.id})
          )
      modalInstance.result.catch (reason) ->
        if reason == 'scheduled'
          DashboardsInfo.getReportDetail(report_id).then((loadedReport) ->
            $scope.report = loadedReport
            $scope.report.variables = loadedReport.variables
            $state.go('reports.dashboards.view.report', { report_id: loadedReport.id})
          )



    $scope.changeReportLevelVar = () ->
      $scope.loaded = false

      DashboardService.saveReportVars($scope.report, true).then( () ->
        $scope.refreshDashboardReport()
      )

    $scope.canViewAllAgents = () ->
      permission = $scope.dashboard.permissions.filter((permission) => permission.person == parseInt(window.DP_PERSON_ID))[0]
      return permission and permission.view_all

    $scope.updateReportVariables = (forceUpdate = false, cb = null) ->

      if !$scope.report || (!$scope.widgets.length && !forceUpdate)|| !$scope.dashboard || !$scope.me || !$scope.groupParams
        if cb then cb()
        return

      vars = []
      $scope.widgets.map((widget) ->
        (widget.widget_variables || []).map((variable) ->
          if vars.map((reportVar) => reportVar.name).indexOf(variable.name) != -1
            return

          if variable.value == 'from_report_value'
            cloneVar = $.extend({}, variable);
            if variable.type == 'dates' && $scope.groupParams[variable.type]
              cloneVar.value = $scope.groupParams[variable.type][Object.keys($scope.groupParams[variable.type])[0]][0]
            else if variable.type == 'values' && $scope.groupParams[variable.type]
              cloneVar.value = Object.keys($scope.groupParams[variable.type][variable.field_type])[0]
            else if $scope.groupParams[variable.type]
              cloneVar.value = $scope.groupParams[variable.type][variable.field_type][Object.keys($scope.groupParams[variable.type][variable.field_type])[0]][0]

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

    $scope.canEdit = () ->
      return false if !$scope.dashboard || !$scope.me.person
      if $scope.me.person.can_admin || $scope.me.person.can_reports
        return true
      for permission in $scope.dashboard.permissions
        return true if (permission.person == $scope.me.person.id || (!permission.person && !permission.team && !permission.department)) && permission.name == 'full'
      return false

    $scope.refreshDashboardReport = () ->
      reloadPromises = []
      $scope.widgets = [];
      reloadPromises.push DashboardsInfo.getReportDetail($scope.report.id, true).then((loadedReport) ->
        $scope.report = loadedReport
      )

      reloadPromises.push DashboardWidgetService.getWidgets(report_id).then((widgets) ->
        $scope.widgets = widgets
      )

      $q.all(reloadPromises).then(->
        $scope.updateReportVariables(false, () => $scope.loaded = true)
      )

    $scope.toggleAutoRefreshReport = () ->
      $scope.autoRefresh = !$scope.autoRefresh
      newVal = if $scope.autoRefresh then 1 else 0
      localStorage.setItem('dp.dashboard.autoRefresh.'+$scope.report_id, newVal)

      if $scope.autoRefresh
        $scope.refreshInterval = setInterval(=>
          $scope.refreshDashboardReport()
        , 10*60*1000)
      else
        clearInterval($scope.refreshInterval)

]
