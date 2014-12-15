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
          , () =>
            console.error('something goes wrong!')

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
            deferred.resolve(dbs)
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

