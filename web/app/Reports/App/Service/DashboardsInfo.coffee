define ['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], (Arrays, Util) ->
  class DashboardsInfo
    constructor: (@Api, @$q) ->
      # this is just a cheap way that controllers
      # can listen on to refresh their state if we change
      # something
      @version_id = 0

      @dashboardList        = []
      @dashboardListPromise = null
      @lastDashboardDetail  = null
      @lastReportDetail     = null

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
    getDashboardList: ->
      d = @$q.defer()

      if @dashboardListPromise
        @dashboardListPromise.then( (l) ->
          d.resolve(l)
        , ->
          d.reject()
        )
        return d.promise

      @dashboardListPromise = d.promise

      @Api.sendGet('/dashboards').then((res) =>
        @dashboardList = Arrays.replaceArray(@dashboardList, res.data)
        for db in @dashboardList
          db.version_id = @version_id
          db.reports_version_id = @version_id
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
    getDashboardDetail: (dashboard_id) ->
      dashboard_id = parseInt(dashboard_id)

      d = @$q.defer()

      if @lastDashboardDetail and @lastDashboardDetail.id == dashboard_id
        d.resolve(@lastDashboardDetail)
        return d.promise

      @Api.sendGet("/dashboards/#{dashboard_id}").then( (resp) =>
        @lastDashboardDetail = resp.data
        @lastDashboardDetail.version_id = @version_id
        @lastDashboardDetail.reports_version_id = @version_id
        d.resolve(resp.data)
      )

      return d.promise

    ###
    # Gets full details about a dashboard (suitable for view/edit)
    #
    # @param {Integer} dashboard_id
    # @return {promise}
    ###
    getReportDetail: (report_id) ->
      report_id = parseInt(report_id)

      d = @$q.defer()

      if @lastReportDetail and @lastReportDetail.id == report_id
        d.resolve(@lastReportDetail)
        return d.promise

      @Api.sendGet("/dashboards/reports/#{report_id}").then( (resp) =>
        @lastReportDetail = resp.data
        d.resolve(resp.data)
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

      dashboard_id = parseInt(dashboard_id)

      @getDashboardList().then((dbs) ->
        db = Arrays.find(dbs, (x) -> x.id == dashboard_id)

        if not db
          d.resolve([])
          return

        d.resolve(db.reports)
      , -> d.reject())

      return d.promise
