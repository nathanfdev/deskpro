define ['DeskPRO/Util/Arrays'], (Arrays) ->
  class DashboardService
    constructor: (Api, $q) ->
      @Api = Api
      @$q = $q
      @data = {}
      @storage = { dbs: [] }

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

    saveDashboard: (db) ->
      url = '/dashboards'
      if db.id
        url += "/#{db.id}";
      @Api
        .sendPost url, {title: db.name, columns: db.options.columns}
        .then (response) =>
            if(response)
              db.id = response.data.id
              if !db.id
                @storage.dbs.push(db)
          , () =>
            console.error('something goes wrong!')

    getDashboards: () ->
      deferred = @$q.defer()
      if @storage.dbs.length == 0
        @getData().then \
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

    saveWidget: (widget) ->
      @Api.sendPost \
        "/dashboards/widgets/#{widget.id}",
        {
          "size_x": widget.newSizeX,
          "size_y": widget.newSizeY
          "col":   widget.newCol
          "row":   widget.newRow
        }

    updateDashboard: (db) ->
      dbId = db.id
      dbName = db.name
      cols = db.options.columns

      @getDashboards().then (dbs) =>

        ind = @getDbIndexById(dbs, dbId)
        dashboard = Arrays.find dbs
        , (v, i) ->
          if v.id is db.id then true else false

        if ind != -1
          dashboard = db
          dashboard.widgets = @widgetService.updateWidgetsSize(db.widgets, cols);
          @saveDashboard(db)

    getData: () ->
      @Api.sendGet('/dashboards')

    deleteDashboard: (dashboard) ->
      promise = @Api.sendDelete("/dashboards/#{dashboard.id}")
      promise.then () =>
        Arrays.removeValue @storage.dbs, dashboard, 1
      return promise



