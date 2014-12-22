define ['DeskPRO/Util/Arrays',], (Arrays) ->
  class DashboardWidgetService
    constructor: (Api, $q) ->
      @Api = Api
      @$q = $q
      @data = {}
      @storage = {}
      @hostname = window.location.origin;
      @selectedSource =
        id: 0

    updateWidgetsSize: (widgets, cols) ->
      ws = []
      ws.push widget for widget, i in widgets when widget != 'last' and widget.sizeX > cols
      return ws

    getWidgetData: (dsName, wtype) ->
      deferred = @$q.defer()

      switch wtype
        when 'graph' then getData = @getGraphs()
        when 'stat'  then getData = @getStats()
        when 'table' then getData = @getTables()
        else getData = false
      if getData
        getData
          .then \
            (rdata) =>
              datum = Arrays.find rdata,
                (v, i, rdata) ->
                  if v? and v.name is dsName
                    return true
              return @getFileData datum.filename, wtype
          .then \
            (fileData)->
              deferred.resolve(fileData)
            , (reason) ->
              deferred.reject(reason.message)
              alert "Unable to load data from file #{reason.statusText}"
      else console.error 'This isn`t widget type you want'

      return deferred.promise

    getReports: () ->
      @Api.sendGet "/reports/builder"

    getFileData: (fileName, wtype) ->
      filePath = fileName

      if wtype == 'table'
        deferred = @$q.defer()
        deferred.resolve(filePath)
        return deferred.promise
      else
        return  @Api.sendGet "#{@hostname}#{filePath}"

    getDbIndexById: (dbs, id) ->
      index = -1
      index = Arrays.findIndex dbs,
      (v) ->
        if v? and v.id is id
          return true
      return index

    saveWidget: (widget) ->
#      console.log(widget);
      @Api.sendPost \
        "/dashboards/widgets/#{widget.id}",
        {
          "size_x": widget.newSizeX,
          "size_y": widget.newSizeY
          "col":   widget.newCol
          "row":   widget.newRow
        }

    setDashboardService: (service) ->
      @dashboardService = service

    addWidget: (dbId, widget) ->
      @Api
      .sendPost "/dashboards/#{dbId}/widgets",
        {
          "name": widget.name
          "size_x": widget.sizeX,
          "size_y": widget.sizeY
          "cols":   widget.cols
          "rows":   widget.rows
          "report": @selectedSource.id
        }
      .then (response) =>
        widget.id = response.data.id
        widget.data = response.data.data
        widget.type = response.data.type
        ind = @getDbIndexById(@dashboardService.storage.dbs, dbId)
        if ind != -1
          @dashboardService.storage.dbs[ind].widgets.push widget
          @storage[widget.id] = widget
        return widget


    removeWidget: (widget) ->
      @Api.sendDelete "/dashboards/widgets/#{widget.id}"

    getWidget: (id) ->
      deferred = @$q.defer()
      if @storage[id]?
        deferred.resolve(@storage[id])
      else
        @Api
          .sendGet "/dashboards/widgets/#{id}"
          .then (resp) =>
            @storage[id] = resp.data.data
            deferred.resolve(@storage[id])
      return deferred.promise
