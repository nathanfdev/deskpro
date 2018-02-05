define ['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], (Arrays, Util) ->
  class DashboardsInfo
    constructor: (@Api, @Api2, @$q) ->
      # this is just a cheap way that controllers
      # can listen on to refresh their state if we change
      # something
      @version_id = 0

      @dashboardList        = []
      @dashboardListPromise = null
      @lastDashboardDetail  = null
      @lastReportDetail     = null
      @agents               = null
      @teams                = null
      @departments          = null

    resetData: ->
      @version_id += 1

      if @dashboardList
        for db in @dashboardList
          db.version_id = @version_id
          db.reports_version_id = @version_id

      if @lastDashboardDetail
        @lastDashboardDetail.version_id = @version_id
        @lastDashboardDetail.reports_version_id = @version_id

      @dashboardList        = []
      @dashboardListPromise = null
      @lastDashboardDetail  = null
      @lastReportDetail     = null

    ###
    # Gets a list of dashboards. This is basic information like id and title.
    # For more information you should use getDashboard().
    #
    # @return {promise}
    ###
    getDashboardList: (includeReports = false) ->
      d = @$q.defer()

      if !includeReports and @dashboardListPromise
        @dashboardListPromise.then( (l) ->
          d.resolve(l)
        , ->
          d.reject()
        )
        return d.promise

      @dashboardListPromise = d.promise

      url = '/dashboards'
      if includeReports
        url += '?include=reports'

      @Api2.sendGet(url).then((res) =>
        @dashboardList = Arrays.replaceArray(@dashboardList, res.data.data)
        for db in @dashboardList
          db.version_id = @version_id
          db.reports_version_id = @version_id
          if includeReports
            db.reports = res.data.linked.reports[db.id]

          d.resolve(@dashboardList)
      , =>
        d.reject()
        @dashboardListPromise = null
      )

      return @dashboardListPromise

    ###
    # Gets full details about a dashboard (suitable for edit)
    #
    # @param {Integer} dashboard_id
    # @return {promise}
    ###
    getDashboardDetail: (dashboard_id, forceReload = false) ->
      dashboard_id = parseInt(dashboard_id)

      d = @$q.defer()

      if @lastDashboardDetail and @lastDashboardDetail.id == dashboard_id and !forceReload
        d.resolve(@lastDashboardDetail)
        return d.promise

      @Api2.sendGet("/dashboards/#{dashboard_id}").then( (resp) =>
        @lastDashboardDetail = resp.data.data
        @lastDashboardDetail.version_id = @version_id
        @lastDashboardDetail.reports_version_id = @version_id
        d.resolve(@lastDashboardDetail)
      )

      return d.promise

    ###
    # Gets full details about a dashboard (suitable for view/edit)
    #
    # @param {Integer} dashboard_id
    # @return {promise}
    ###
    getReportDetail: (report_id, forceReload = false) ->
      report_id = parseInt(report_id)

      d = @$q.defer()

      if @lastReportDetail and @lastReportDetail.id == report_id and ! forceReload
        d.resolve(@lastReportDetail)
        return d.promise

      @Api2.sendGet("/dashboard_reports/#{report_id}").then( (resp) =>
        @lastReportDetail = resp.data.data
        d.resolve(resp.data.data)
        return d.promise
      )

      return d.promise

    ###
    # Gets a list of report id/title that exist on a dashboard.
    #
    # @return {promise}
    ###
    getReportsList: (dashboard_id) ->
      d = @$q.defer()
      @Api2.sendGet('/dashboards/'+dashboard_id+'/reports').then( (resp) => d.resolve(resp.data.data))

      return d.promise

    getAgents: () =>
      d = @$q.defer()

      if(@agents)
        d.resolve(@agents)
      else
        @Api2.sendGet('/agents').then( (res) ->
          agents = res.data.data
          agents.map((agent) ->
            agent.avatar.url = (agent.avatar.url_pattern || agent.avatar.default_url_pattern).replace('{{IMG_SIZE}}', 20)
          )
          @agents = agents

          d.resolve(@agents)
        )

      return d.promise

    getAgentTeams: () =>
      d = @$q.defer()
      if(@teams)
        d.resolve(@teams)
      else
        @Api2.sendGet('/agent_teams').then( (res) ->
          teams = res.data.data
          teams.map((team) ->
            team.avatar.url = (team.avatar.url_pattern || team.avatar.default_url_pattern).replace('{{IMG_SIZE}}', 20)
          )
          @teams = teams
          d.resolve(@teams)
        )

      return d.promise

    getDepartments: () =>
      d = @$q.defer()
      if(@departments)
        d.resolve(@departments)
      else
        @Api2.sendGet('/ticket_departments').then( (res) ->
          @departments = res.data.data
          d.resolve(@departments)
        )

      return d.promise

    getMe: () ->
      d = @$q.defer()
      @Api2.sendGet('/me').then( (res) ->
        d.resolve res.data.data
      )

      return d.promise
