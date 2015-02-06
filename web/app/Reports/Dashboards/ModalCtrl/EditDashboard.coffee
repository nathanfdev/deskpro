define ['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], (Arrays, Util) -> [
  '$scope', '$q', '$modalInstance', 'dashboard_id', 'modal_options', 'DashboardsInfo', 'DashboardService', 'DashboardPermissionsService'
  ($scope, $q, $modalInstance, dashboard_id, modal_options, DashboardsInfo, DashboardService, DashboardPermissionsService) ->
    $scope.loaded = false
    $scope.dashboard = null
    $scope.reports = []
    $scope.activeTab = 'info'
    $scope.is_new = false

    $scope.did_edit_reports = false

    ####################################################################################################################
    # LOADING
    ####################################################################################################################

    if modal_options.activeTab
      $scope.activeTab = modal_options.activeTab

    load_promises = []

    load_promises.push DashboardsInfo.getDashboardList().then( (dbs) ->
      $scope.dashboards = dbs
    )

    if dashboard_id
      load_promises.push DashboardsInfo.getDashboardDetail(dashboard_id).then( (db) ->
        $scope.dashboard = db
        $scope.reports = db.reports.map((r) -> {
          id: r.id
          title: r.title,
        })
      )
    else
      $scope.is_new = true
      $scope.dashboard =
        title: '',
        reports: [{
          id: Util.uid('new'),
          isNew: true,
          isAdded: true
          title: ''
        }],
        is_default: false,
        permissions: []
      DashboardPermissionsService.getDashboardPermissions().then (response) ->
          $scope.dashboard.permissions = response.data

    $q.all(load_promises).then(-> $scope.loaded = true)

    ####################################################################################################################
    # Sortable config
    ####################################################################################################################

    $scope.sortableOptions = {
      axis: 'y',
      handle: '.drag-handle',
      update: ->
        console.log($scope.reports)
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
    # Clones all reports from a particular dashboard
    ###
    $scope.cloneDashboard = (db) ->
      $scope.show_clone_menu = false
      for r in db.reports
        $scope.cloneReport(db, r)

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

    ####################################################################################################################
    # SAVE
    ####################################################################################################################

    doSaveDashboard = ->
      d = $q.defer()

      dashboard = $scope.dashboard
      data = dashboard;
      data.reports = $scope.reports
      DashboardService
      .saveDashboard dashboard
      .then (saved) ->
        d.resolve saved

#      window.setTimeout(->
#        d.resolve()
#      , 1800)

      d.promise
  ]