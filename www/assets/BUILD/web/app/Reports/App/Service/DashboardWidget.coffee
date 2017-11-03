define ['DeskPRO/Util/Arrays',], (Arrays) ->
  class DashboardWidgetService

    constructor: (Api, $q) ->
      @Api = Api
      @$q = $q
      @data = {}
      @storage = {reports: [], labels: [], reportsByLabels: {}}
      @groupParams = []
      @widgets = {}

      @Api.sendGet('reports/builder/group-params').then (response) =>
        @groupParams = response.data

    getIndexById: (storage, id) ->
      index = -1
      index = Arrays.findIndex storage,
        (v) ->
          if v? and v.id is id
            return true
      return index

    updateWidgetsSize: (widgets, cols) ->
      ws = []
      ws.push widget for widget, i in widgets when widget != 'last' and widget.sizeX > cols
      return ws

    getReports: () ->
      deferred = @$q.defer()
      if @storage.reports.length == 0
        @Api
          .sendGet "/dashboards/widgets/reports/list"
          .then (result) =>
            @storage.reports = result.data.reports
            @storage.labels = result.data.labels
            deferred.resolve @storage
            return deferred.promise
      else
        deferred.resolve @storage
        return deferred.promise

    isActiveLabel: (storage, label) ->
      index = -1
      index = Arrays.findIndex storage,
        (v) ->
          return true if v? and v is label
      if index > 0
        return true
      else
        return false

    saveWidget: (widget) ->
      @Api.sendPost \
        "/dashboards/widgets/#{widget.id}",
        {
          "size_x": if widget.newSizeX? then widget.newSizeX else widget.sizeX
          "size_y": if widget.newSizeY? then widget.newSizeY else widget.sizeY
          "col":   if widget.newCol? then widget.newCol else widget.col
          "row":   if widget.newRow? then widget.newRow else widget.row
          "title":   widget.title
        }

    setDashboardService: (service) ->
      @dashboardService = service

    addWidget: (report, widget) ->
      url = "/dashboards/#{report.id}/widgets"
      data = widget
      @Api
      .sendPostJson url, data
      .then (response) =>
        newWidget = response.data
        report.widgets.push newWidget


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
