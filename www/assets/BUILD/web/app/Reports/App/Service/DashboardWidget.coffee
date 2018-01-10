define ['DeskPRO/Util/Arrays'], (Arrays) ->
  class DashboardWidgetService

    constructor: (Api, Api2, $q) ->
      @Api = Api
      @Api2 = Api2
      @$q = $q
      @data = {}
      @storage = {reports: [], labels: [], reportsByLabels: {}}
      @groupParams = []
      @widgets = {}

      @Api2.sendGet('report_widgets/group-params').then (response) =>
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
        @Api2
          .sendGet "/report_widgets"
          .then (result) =>
            @storage.reports = result.data.data
            @storage.labels = []
            for report in result.data.data
              for label in report.labels
                if @storage.labels.indexOf(label) == -1
                  @storage.labels.push(label)
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
          "size_x":  if widget.newSizeX? then widget.newSizeX else widget.sizeX
          "size_y":  if widget.newSizeY? then widget.newSizeY else widget.sizeY
          "col":     if widget.newCol? then widget.newCol else widget.col
          "row":     if widget.newRow? then widget.newRow else widget.row
          "title":   widget.title
          "options": widget.options
        }

    setDashboardService: (service) ->
      @dashboardService = service

    addWidget: (report, widget) ->
      url = "/dashboards/#{report.id}/widgets"
      data = widget
      deferred = @$q.defer()

      @Api
      .sendPostJson url, data
      .then (response) =>
        newWidget = response.data
        report.widgets.push newWidget
        deferred.resolve()

      return deferred.promise


    testWidget: (reportWidget) ->
      url = "/report_widgets/test/#{reportWidget.id}?include=rendered_result&inline_sideloads=1"
      dataToSend =
        display_types: reportWidget.display_types,
        variables:     reportWidget.variables,
        input_mode:    'form',
        query_parts:   reportWidget.query_parts

      @Api2
        .sendPostJson url, dataToSend


    removeWidget: (widget) ->
      @Api.sendDelete "/dashboards/widgets/#{widget.id}"

    getWidget: (id) ->
      deferred = @$q.defer()

      @Api
        .sendGet "/dashboards/widgets/#{id}"
        .then (resp) =>
          deferred.resolve(resp.data.data)
      return deferred.promise
