define ['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], (Arrays, Util) -> [
  '$scope', '$q', '$modal', '$modalInstance', 'dashboard_id', 'modal_options', 'DashboardsInfo', 'DashboardService', 'Growl'
  ($scope, $q, $modal, $modalInstance, dashboard_id, modal_options, DashboardsInfo, DashboardService, Growl) ->
    $scope.loaded = false
    $scope.dashboard = null
    $scope.reports = []
    $scope.agents = []
    $scope.activeTab = 'info'
    $scope.is_new = false
    $scope.shareLinks = []
    $scope.shareLink = null
    $scope.shareLinkView = null

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
    load_promises.push DashboardsInfo.getAgentTeams().then( (teams) ->
      $scope.teams = teams
    )
    load_promises.push DashboardsInfo.getDepartments().then( (departments) ->
      $scope.departments = departments
    )

    if dashboard_id
      load_promises.push DashboardsInfo.getDashboardDetail(dashboard_id).then( (db) ->
        $scope.dashboard = angular.copy db
        $scope.dashboard.permissions = {agent: [], department: [], team: [], all: ''}
        for permission in db.permissions
          if !permission.person && !permission.department && !permission.team
            $scope.dashboard.permissions.all = permission.name
          else if permission.person
            $scope.dashboard.permissions.agent.push angular.copy permission
          else if permission.team
            $scope.dashboard.permissions.team.push angular.copy permission
          else if permission.department
            $scope.dashboard.permissions.department.push angular.copy permission

      )
      load_promises.push DashboardsInfo.getReportsList(dashboard_id).then( (reports) ->
        $scope.reports = reports
      )
      load_promises.push DashboardService.getShareableLinks(dashboard_id).then( (shareableLinks) ->
        $scope.shareLinks = shareableLinks
      )
    else
      $scope.is_new = true
      $scope.dashboard =
        title: '',
        reports: [{
          title: ''
        }],
        is_default: false,
        is_agent: false,
        permissions: {agent: [], team: [], department: [], all: ''}

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

    $scope.getInitials = (agent) ->
      return '?' if !agent?
      first    = agent.first_name
      last     = agent.last_name
      initials = (if first && first.length then first[0] else '') + (if last && last.length then last[0] else '')

      return initials

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
      doSaveDashboard().then( \
        () ->
          DashboardsInfo.resetData()
          if !$scope.is_new
            $scope.dashboard.version_id++
            if $scope.did_edit_reports then $scope.dashboard.reports_version_id

          $scope.saving = false
          $modalInstance.close()
        , () ->
          $scope.saving = false
      )

    # All
    $scope.canAllAgentsViewDashboard = () ->
      return $scope.dashboard.permissions.all

    $scope.canAllAgentsEditDashboard = () ->
      return $scope.dashboard.permissions.all? == 'full'

    $scope.toggleAllAgentsViewDashboard = () ->
      if $scope.dashboard.permissions.all
        # turn off
        $scope.dashboard.permissions.all = ''
        return

      if !$scope.dashboard.permissions.all
        $scope.dashboard.permissions.all = 'view'
        return

    $scope.toggleAllAgentsEditDashboard = () ->
      if $scope.dashboard.permissions.all == 'full'
        # turn off
        $scope.dashboard.permissions.all = 'view'
        return
      else
        $scope.dashboard.permissions.all = 'full'
        return

    # Agents
    $scope.canAgentViewDashboard = (agentId) ->
      return (($scope.dashboard.permissions.agent || []).filter((permission) => permission.person == agentId).length > 0) || $scope.dashboard.permissions.all

    $scope.canAgentEditDashboard = (agentId) ->
      return (($scope.dashboard.permissions.agent || []).filter((permission) => permission.person == agentId and permission.name == 'full').length > 0) || $scope.dashboard.permissions.all == 'full'

    $scope.canViewAllAgents = (agentId) ->
      permission = ($scope.dashboard.permissions.agent || []).filter((permission) => permission.person == agentId)[0]
      if !permission
        return false

      return permission.view_all

    $scope.toggleAgentViewDashboard = (agentId) ->
      permission = $scope.dashboard.permissions.agent.filter((permission) => permission.person == agentId)[0]
      if !permission
        $scope.addAgentViewDashboard(agentId, permission)
      else
        $scope.removeAgentViewDashboard(agentId, permission)

    $scope.removeAgentViewDashboard = (agentId, permission = false) ->
      if $scope.dashboard.permissions.all
        return
      if !permission
        permission = $scope.dashboard.permissions.agent.filter((permission) => permission.person == agentId)[0]
      if permission
        $scope.dashboard.permissions.agent.splice($scope.dashboard.permissions.agent.indexOf(permission), 1)

    $scope.addAgentViewDashboard = (agentId, permission = false) ->
      if $scope.dashboard.permissions.all != ''
        return
      if !permission
        permission = $scope.dashboard.permissions.agent.filter((permission) => permission.person == agentId)[0]
      if !permission
        $scope.dashboard.permissions.agent.push({
          name: 'view'
          person: agentId
          view_all: false
          team: null
          department: null
        })

    $scope.toggleAgentEditDashboard = (agentId) ->
      permission = $scope.dashboard.permissions.agent.filter((permission) => permission.person == agentId)[0]
      if !permission
        $scope.dashboard.permissions.agent.push({
          name: 'full'
          person: agentId
          view_all: false
          team: null
          department: null
        })
      else if permission.name == 'view'
        permission.name = 'full'
      else if permission.name == 'full'
        permission.name = 'view'

    $scope.toggleViewAllAgents = (agentId) ->
      permission = $scope.dashboard.permissions.filter((permission) => permission.person == agentId)[0]
      if !permission
        $scope.dashboard.permissions.agent.push({
          name: 'view'
          person: agentId
          team: null
          department: null
          view_all: true
        })
      else
        permission.view_all = !permission.view_all


    # Teams
    $scope.canTeamViewDashboard = (teamId) ->
      return ($scope.dashboard.permissions.team || []).filter((permission) => permission.team == teamId).length > 0

    $scope.canTeamEditDashboard = (teamId) ->
      return ($scope.dashboard.permissions.team || []).filter((permission) => permission.team == teamId and permission.name == 'full').length > 0

    $scope.toggleTeamViewDashboard = (teamId) ->
      permission = $scope.dashboard.permissions.team.filter((permission) => permission.team == teamId)[0]
      if !permission
        $scope.dashboard.permissions.team.push({
          name: 'view'
          person: null
          department: null
          team: teamId
        })
      else
        $scope.dashboard.permissions.team.splice($scope.dashboard.permissions.team.indexOf(permission), 1)

    $scope.toggleTeamEditDashboard = (teamId) ->
      permission = $scope.dashboard.permissions.team.filter((permission) => permission.team == teamId)[0]
      if !permission
        $scope.dashboard.permissions.team.push({
          name: 'full'
          person: null
          department: null
          team: teamId
        })
      else if permission.name == 'view'
        permission.name = 'full'

    $scope.openNewShareLinkForm = () ->
      $scope.shareLink = {
        dashboard: $scope.dashboard.id
        title: ''
        default_report: null
        who_can_use: 'anyone'
        ip_whitelist: ''
      }
      $scope.shareLinkView = 'new'

    $scope.openShareLinkForm = (shareLink) ->
      $scope.shareLink = angular.copy shareLink
      $scope.shareLinkView = 'share'

    $scope.openEditShareLinkForm = (shareLink) ->
      $scope.shareLink = shareLink
      $scope.shareLinkView = 'edit'

    $scope.saveShareLink = () ->
      $scope.saving = true
      if !$scope.shareLink
        return
      if $scope.shareLink.id
        promise = DashboardService.updateShareLink($scope.shareLink)
      else
        promise = DashboardService.createShareLink($scope.shareLink)
        promise.then((newSharedLink) ->
          $scope.shareLinks.push newSharedLink
        )

      promise.then(
        () ->
          $scope.shareLink = null
          $scope.shareLinkView = null
          $scope.saving = false
          Growl.success('Shared link is saved')
        (response) ->
          if (response.errors?.fields?.title?.errors[0])
            $scope.error = 'Title could not be blank'
          $scope.saving = false
      )
      
    $scope.deleteShareLink = (shareLink) ->
      modalInstance = $modal.open {
        templateUrl: "ReportsInterfaceBundle:Index:modal-confirm.html",
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.title   = 'Confirm discard'
          $scope.message = 'Are you sure you want to delete this link?'

          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.confirm = ->
            $modalInstance.close()
        ]
      }
      modalInstance.result.then(
        () =>
          DashboardService.deleteDashboardShareableLink(shareLink).then ->
            Arrays.removeValue $scope.shareLinks, shareLink
      )

    $scope.createShortUrlForShareLink = (shareLink) ->
      DashboardService.createShortUrlForShareLink(shareLink).then( (shortUrl) ->
        shareLink.short_url = shortUrl
        for link in $scope.shareLinks
          if link.id == shareLink.id
            link.short_url = shortUrl
      )

    $scope.successAlert = (message) ->
      Growl.success(message)

    ####################################################################################################################
    # SAVE
    ####################################################################################################################

    doSaveDashboard = ->
      d = $q.defer()

      data = angular.copy $scope.dashboard;
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

      data.permissions = []
      data.permissions.push permission for permission in $scope.dashboard.permissions.agent
      data.permissions.push permission for permission in $scope.dashboard.permissions.team
      data.permissions.push permission for permission in $scope.dashboard.permissions.department
      if $scope.dashboard.permissions.all
        data.permissions.push {person: null, team: null, department: null, name: $scope.dashboard.permissions.all}

      DashboardService
      .saveDashboard data
      .then \
        (saved) ->
          $scope.error = null
          d.resolve saved
        , (response) ->
          if (response.errors?.fields?.title?.errors[0])
            $scope.error = 'Dashboard title could not be blank'
          d.reject()
      d.promise
  ]