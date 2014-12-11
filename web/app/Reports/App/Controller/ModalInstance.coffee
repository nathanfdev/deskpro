define [], () ->
  class Reports_Dashboard_Ctrl_ModalInstance
    @CTRL_ID   = 'Reports_Dashboard_Ctrl_ModalInstance'
    @CTRL_AS   = 'ReportsModalInstanceCtrl'
    @DEPS      = ['$scope', '$modalInstance', 'db']

    @newWidget:
      title: ''
      sizeX: 0
      sizeY: 1

    @newDashboard:
      name: ''
      cols: ''

    @dbname = ''
    @dbcolumns = 0

    init: ->
      @$scope.wtype = ''
      @$scope.selectedSource = {}
      @$scope.selectedSource.name = 'Select source'
      @service = @DataService.get('DashboardService')
      @widgetService = @DataService.get('DashboardWidgetService')
      @service.setWidgetService(@widgetService)

      if @db?
        @dbname = @db.name
        @dbcolumns = @db.options.columns

      @widgetService
        .getReports()
        .then \
          (response) =>
            @$scope.dataSources = response.data.reports
            console.log(@$scope.dataSources)
          ,(reason) ->
              alert('Unable to load data from file. ' + reason.statusText)
              console.error('wow, take it easy, laddie')

    createWidget: () ->
      widget =
        title: @newWidget.title,
        wtype: @$scope.wtype,
        dsName: @$scope.selectedSource.name,
        x: @newWidget.sizeX,
        y: @newWidget.sizeY

      @$modalInstance.close(widget);

    chooseWtype: (wtype) ->
      @$scope.wtype = wtype;

    cancel: () ->
      @$modalInstance.dismiss('cancel')

    setSource: (dataSource) ->
      @widgetService.selectedSource.id = dataSource.id

    updateDashboard: () ->
      @db.name = @dbname
      @db.options.columns = @dbcolumns
      @$modalInstance.close(@db)

    createDashboard: () ->
      dashboard =
        name: @newDashboard.name,
        options:
          margins: [20, 20],
          columns: parseInt(@newDashboard.cols),
          floating: true,
          swapping: false,
          draggable:
            handle: 'h3'
          resizable:
            enabled: true,
            handles: ['n', 's', 'w', 'ne', 'se', 'sw', 'nw'],
          #start: function (event, $element, widget) { }, // optional callback fired when resize is started,
          #resize: function (event, $element, widget) { }, // optional callback fired when item is resized,
            stop: @updateWidgetSize # optional callback fired when item is finished resizing
        widgets: []

      @$modalInstance.close(dashboard)

    initialLoad: ->

    updateWidgetSize: (event, $element, widget) ->
      w = widget;
      k = w.$$hashKey;

      alert 'found' for widget, i in @$scope.dashboard.widgets when widget.$$hashKey == k



    Reports_Dashboard_Ctrl_ModalInstance.EXPORT_CTRL()