define ['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], (Arrays, Util) -> [
  '$scope', '$q', '$modalInstance', 'dashboard_id', 'modal_options', 'DashboardsInfo', 'DashboardService'
  ($scope, $q, $modalInstance, dashboard_id, modal_options, DashboardsInfo, DashboardService) ->
    $scope.loaded = false
    $scope.dashboard = null
    $scope.reports = []
    $scope.agents = []
    $scope.activeTab = 'info'
    $scope.is_new = false

    $scope.did_edit_reports = false

    ####################################################################################################################
    # LOADING
    ####################################################################################################################

    if modal_options.activeTab
      $scope.activeTab = modal_options.activeTab

    load_promises = []
    load_promises.push DashboardsInfo.getDashboardList(true).then( (dbs) ->
      $scope.dashboards = dbs
    )
    load_promises.push DashboardsInfo.getAgents().then( (agents) ->
      $scope.agents = agents
    )

    if dashboard_id
      load_promises.push DashboardsInfo.getDashboardDetail(dashboard_id).then( (db) ->
        $scope.dashboard = db
      )
      load_promises.push DashboardsInfo.getReportsList(dashboard_id).then( (reports) ->
        $scope.reports = reports
      )
    else
      $scope.is_new = true
      $scope.dashboard =
        title: '',
        reports: [{
          title: ''
        }],
        is_default: false,
        permissions: []

    $q.all(load_promises).then(-> $scope.loaded = true)

    ####################################################################################################################
    # Sortable config
    ####################################################################################################################

    $scope.sortableOptions = {
      axis: 'y',
      handle: '.drag-handle',
      update: ->

    }

    ####################################################################################################################
    # UI handlers
    ####################################################################################################################

    $scope.cancel = -> $modalInstance.dismiss('cancel')

    ###
    # Removes a report from the dashboard
    ###
    $scope.removeReport = (report) ->
      $scope.did_edit_reports = true
      $scope.reports = $scope.reports.filter((r) -> r.id != report.id)

    ###
    # Adds a blank report to the dashboard
    ###
    $scope.addReport = ->
      $scope.did_edit_reports = true
      $scope.reports.push({
        id: Util.uid('new'),
        isNew: true,
        isAdded: true
        title: ''
      })

    ###
    # Clones all reports from specified dashboard
    ###
    $scope.cloneDashboard = (db) ->
      $scope.show_clone_menu = false
      DashboardsInfo.getReportsList(db.id).then( (reports) ->
        for r in reports
         $scope.cloneReport(db, r)
      )

    ###
    # Clones a report from an existing dashboard
    ###
    $scope.cloneReport = (db, r) ->
      $scope.did_edit_reports = true
      $scope.show_clone_menu = false
      $scope.reports.push({
        id: Util.uid('new'),
        cloneId: r.id,
        isAdded: true,
        title: r.title,
        fromDashboardTitle: db.title
      })

    ###
    # Opens clone menu
    ###
    $scope.openCloneMenu = ->
      $scope.show_clone_menu = true

    ###
    # Closes clone menu
    ###
    $scope.closeCloneMenu = ->
      $scope.show_clone_menu = false

    ###
    # Save the form
    ###
    $scope.saveDashboard = ->
      $scope.saving = true
      doSaveDashboard().then(->
        DashboardsInfo.resetData()
        if !$scope.is_new
          $scope.dashboard.version_id++
          if $scope.did_edit_reports then $scope.dashboard.reports_version_id

        $scope.saving = false
        $modalInstance.close()
      )

    $scope.canAgentViewDashboard = (agentId) ->
      return ($scope.dashboard.permissions || []).filter((permission) => permission.person == agentId).length > 0

    $scope.canAgentEditDashboard = (agentId) ->
      return ($scope.dashboard.permissions || []).filter((permission) => permission.person == agentId and permission.name == 'full').length > 0

    $scope.toggleAgentViewDashboard = (agentId) ->
      permission = $scope.dashboard.permissions.filter((permission) => permission.person == agentId)[0]
      if !permission
        $scope.dashboard.permissions.push({
          name: 'view'
          person: agentId
        })
      else
        $scope.dashboard.permissions.splice($scope.dashboard.permissions.indexOf(permission), 1)

    $scope.toggleAgentEditDashboard = (agentId) ->
      permission = $scope.dashboard.permissions.filter((permission) => permission.person == agentId)[0]
      if !permission
        $scope.dashboard.permissions.push({
          name: 'full'
          person: agentId
        })
      else if permission.name == 'view'
        permission.name = 'full'

    ####################################################################################################################
    # SAVE
    ####################################################################################################################

    doSaveDashboard = ->
      d = $q.defer()

      dashboard = $scope.dashboard
      data = dashboard;
      data.reports = []
      for report in $scope.reports
        reportData = {
          title: report.title
        }

        if !report.isAdded
          reportData.id = report.id
        if report.cloneId
          reportData.clone_id = report.cloneId

        data.reports.push reportData

      DashboardService
      .saveDashboard dashboard
      .then (saved) ->
        d.resolve saved
      d.promise
  ]