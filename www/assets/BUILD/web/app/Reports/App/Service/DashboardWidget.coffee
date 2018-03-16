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
      ws.push widget for widget, i in widgets when widget != 'last' and widget.size_x > cols
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
      @Api2.sendPutJson \
        "/dashboard_report_widgets/#{widget.id}",
        {
          "size_x":  if widget.newSizeX? then widget.newSizeX else widget.size_x
          "size_y":  if widget.newSizeY? then widget.newSizeY else widget.size_y
          "col":     if widget.newCol? then widget.newCol else widget.col
          "row":     if widget.newRow? then widget.newRow else widget.row
          "title":   widget.title
          "options": widget.options
        }

    setDashboardService: (service) ->
      @dashboardService = service

    addWidget: (report, widget) ->
      widgetVars = []
      for name,variable of widget.variables
        variable.name = name
        widgetVars.push(variable)

      url = "/dashboard_report_widgets"
      data = {
        title: widget.title
        type: widget.type
        col: widget.col
        row: widget.row
        size_x: widget.sizeX
        size_y: widget.sizeY
        report: report.id
        widget_variables: widgetVars
      }

      if widget.widget_id == 'advanced'
        data.js_code = widget.js_code
      else
        data.widget = widget.widget_id

      deferred = @$q.defer()

      @Api2
      .sendPostJson url, data
      .then (response) =>
        deferred.resolve(response.data.data)
      .catch (response) =>
        deferred.reject(response.data)

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
      @Api2.sendDelete "/dashboard_report_widgets/#{widget.id}"

    getWidgets: (reportId) ->
      deferred = @$q.defer()

      @Api2
      .sendGet "/dashboard_reports/#{reportId}/widgets?include=rendered_result&inline_sideloads=1"
      .then (resp) =>
        widgets = resp.data.data;
        widgets.map((widget) =>
          widget.sizeX = widget.size_x
          widget.sizeY = widget.size_y
        )

        deferred.resolve(widgets)
      return deferred.promise

    getWidget: (id) ->
      deferred = @$q.defer()

      @Api2
        .sendGet "/dashboard_report_widgets/#{id}?include=rendered_result&inline_sideloads=1"
        .then (resp) =>
          widget = resp.data.data;
          widget.sizeX = widget.size_x
          widget.sizeY = widget.size_y

          deferred.resolve(widget)
      return deferred.promise
