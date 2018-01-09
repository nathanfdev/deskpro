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
            for report in result.data.data
              for label in report.labels
                if @storage.labels.indexOf label == -1
                  @storage.labels.push label
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
      url = "/reports/widget/test/#{reportWidget.id}"
      dataToSend =
        report:
          title:         reportWidget.title,
          description:   reportWidget.desc,
          display_types: reportWidget.display_types,
          variables:     reportWidget.variables,
          labels:        reportWidget.labels,
        parts:
          select:  reportWidget.select,
          from:    reportWidget.from,
          where:   reportWidget.where,
          splitBy: reportWidget.splitBy,
          groupBy: reportWidget.groupBy,
          orderBy: reportWidget.orderBy,
          limit:   reportWidget.limit,
          offset:  reportWidget.offset

      if (reportWidget.jsonTable?)
        dataToSend.report.jsonTable = reportWidget.jsonTable

      @Api
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
