define ['DeskPRO/Util/Arrays',], (Arrays) ->
  class DashboardWidgetService
    constructor: (Api, $q) ->
      @Api = Api
      @$q = $q
      @data = {}
      @storage = {reports: [], labels: [], reportsByLabels: {}}
      @groupParams = []
      @widgets = {}
      @hostname = window.location.origin;
      @selectedSource =
        id: 0
      @wdata =
        "type": "serial",
        "theme": "none",
        "dataProvider": [
          {
            "country": "USA",
            "visits": 2025
          },
          {
            "country": "China",
            "visits": 1882
          },
          {
            "country": "Japan",
            "visits": 1809
          },
          {
            "country": "Germany",
            "visits": 1322
          },
          {
            "country": "UK",
            "visits": 1122
          },
          {
            "country": "France",
            "visits": 1114
          },
          {
            "country": "India",
            "visits": 984
          },
          {
            "country": "Spain",
            "visits": 711
          },
          {
            "country": "Netherlands",
            "visits": 665
          },
          {
            "country": "Russia",
            "visits": 580
          },
          {
            "country": "South Korea",
            "visits": 443
          },
          {
            "country": "Canada",
            "visits": 441
          },
          {
            "country": "Brazil",
            "visits": 395
          }
        ],
        "valueAxes": [{
          "gridColor":"#FFFFFF",
          "gridAlpha": 0.2,
          "dashLength": 0
        }],
        "gridAboveGraphs": true,
        "startDuration": 1,
        "graphs": [{
          "balloonText": "[[category]]: <b>[[value]]</b>",
          "fillAlphas": 0.8,
          "lineAlpha": 0.2,
          "type": "column",
          "valueField": "visits"
        }],
        "chartCursor": {
          "categoryBalloonEnabled": false,
          "cursorAlpha": 0,
          "zoomable": false
        },
        "categoryField": "country",
        "categoryAxis": {
          "gridPosition": "start",
          "gridAlpha": 0,
          "tickPosition":"start",
          "tickLength":20
        },
        "exportConfig":{
          "menuTop": 0,
          "menuItems": [{
            "icon": '/lib/3/images/export.png',
            "format": 'png'
          }]
        }

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
          "size_x": widget.newSizeX,
          "size_y": widget.newSizeY
          "col":   widget.newCol
          "row":   widget.newRow
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
        newWidget.data = @wdata
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
