define ['DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], (Arrays, Util) ->
  class DashboardsInfo
    constructor: (@Api, @$q) ->
      @dashboardList = []
      @dashboardListPromise = null

    resetData: ->
      @dashboardList = []
      @dashboardListPromise = null

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
        d.resolve(@dashboardList)
      , =>
        d.reject()
        @dashboardListPromise = null
      )

      return @dashboardListPromise

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
