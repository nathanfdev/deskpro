define ['DeskPRO/Util/Arrays'], (Arrays) ->
  class DashboardService
    constructor: (Api, $q) ->
      @Api = Api
      @$q = $q
      @data = {}
      @storage = { dbs: [], reports: [] }

    ping: ->
      console.log('this is widget service!')

    setWidgetService: (service) ->
      @widgetService = service
      @widgetService.setDashboardService(@)


    getDbIndexById: (dbs, id) ->
      index = -1
      index = Arrays.findIndex dbs,
      (v) ->
        if v? and v.id is id
          return true
      return index


    ###
    # Operations about dashboards
    ###
    saveDashboard: (dashboard) ->
      deferred = @$q.defer()
      url = '/dashboards'
      oldOne = false
      if dashboard.id
        oldOne = true
        url += "/#{dashboard.id}";
      @Api
        .sendPost url, {title: dashboard.title}
        .then (response) =>
            if(response)
              dashboard.id = response.data.id
              if !oldOne
                @storage.dbs.push(dashboard)
              deferred.resolve dashboard
          , () =>
            console.error 'something goes wrong!'
      deferred.promise

    cloneDashboard: (dashboard) ->
      deferred = @$q.defer()
      url = "/dashboards/clone/#{dashboard.id}"
      @Api
        .sendPost url
        .then (response) =>
          if(response)
            clonedOne = response.data
            @storage.dbs.push clonedOne
            deferred.resolve clonedOne
        , () =>
          console.error('something goes wrong!')
      deferred.promise

    getDashboards: () ->
      deferred = @$q.defer()
      if @storage.dbs.length == 0
        @getDashboardsData().then \
          (resp) =>
            dbs = []
            if resp? and resp.data.length > 0
#              dbs.push(el = @widgetService.fillElement element) for element in resp.data
              dbs.push element for element in resp.data
            @storage.dbs = dbs;
            deferred.resolve(@storage.dbs)
      else deferred.resolve(@storage.dbs)

      return deferred.promise

    getDashboard: (dashboard) ->
      deferred = @$q.defer()
      dashboardIndex = Arrays.findIndex @storage.dbs
      , (v, i) ->
        if v.id is dashboard.id then true else false
      @Api
        .sendGet "/dashboards/#{dashboard.id}"
        .then (resp) =>
          @storage.dbs[dashboardIndex] = resp.data
          deferred.resolve(resp.data)
      return deferred.promise

    getDashboardsData: () ->
      @Api.sendGet('/dashboards')

    deleteDashboard: (dashboard) ->
      promise = @Api.sendDelete("/dashboards/#{dashboard.id}")
      promise.then () =>
        Arrays.removeValue @storage.dbs, dashboard, 1
      return promise

    ###
    # Operations about reports
    ###
    getReportsData: () ->
      @Api.sendGet('/dashboards/reports')

    getReports: () ->
      deferred = @$q.defer();
      if @storage.reports.length == 0
        @getReportsData().then \
          (resp) =>
            reports = []
            if resp? and resp.data.length > 0
              reports.push element for element in resp.data
            @storage.reports = reports;
            deferred.resolve(reports)
      else deferred.resolve(@storage.reports)

    removeReport: (report) ->
      deferred = @$q.defer()
      url = "/dashboards/reports/#{report.id}"
      @Api
      .sendDelete url
      .then (response) =>
        if(response)
          db_id = @getDbIndexById @storage.dbs, report.dashboard_id
          Arrays.removeValue @storage.dbs[db_id].reports, report
          deferred.resolve @storage.dbs[db_id].reports
      , () =>
        console.error('something goes wrong!')
      deferred.promise

    createReport: (report) ->
      deferred = @$q.defer()
      url = "/dashboards/reports/#{report.dashboard_id}"

      newReport =
        title: report.title
        columns: if report.columns? then report.columns else 0
        loaded: false
        widgets: []

      @Api
      .sendPost url, newReport
      .then (response) =>
        if(response)
          newOne = response.data
          @storage.reports.push newOne
          deferred.resolve newOne
      , () =>
        console.error 'something goes wrong!'
      deferred.promise

    cloneReport: (report, dashboard_id) ->
      deferred = @$q.defer()
      url = "/dashboards/reports/clone/#{report.id}/#{dashboard_id}"

      newReport =
        title: report.title
        columns: if report.columns? then report.columns else 0
        loaded: false
        widgets: []

      @Api
      .sendPost url, newReport
      .then (response) =>
        if(response)
          clonedOne = response.data
          db_index = @getDbIndexById @storage.dbs, response.data.dashboard_id
          clonedOne.dashboard_id = dashboard_id
          clonedOne.cloned = true
          @storage.reports.push clonedOne
          deferred.resolve clonedOne
      , () =>
        console.error 'something goes wrong!'
      deferred.promise


