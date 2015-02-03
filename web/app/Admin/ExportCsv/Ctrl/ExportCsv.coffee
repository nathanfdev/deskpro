define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ExportCsv_Ctrl_ExportCsv extends Admin_Ctrl_Base

    @CTRL_ID   = 'Admin_ExportCsv_Ctrl_ExportCsv'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['Api', '$interval']



    init: ->
      @$scope.status = null
      @$scope.offset = 0
      @$scope.files = []

      @$scope.start = =>
        @Api.sendPost('/export/start').then (data) =>
          @$scope.status = data.data.status
          @$scope.offset = data.data.offset

      @$scope.stop = =>
        @Api.sendPost('/export/stop').then (data) =>
          @$scope.status = data.data.status
          @$scope.offset = data.data.offset

      @$scope.$watch 'status', (val) =>

        if 'running' == val || 'queued' == val
          if !@interval
            @interval = @$interval(
              => @getStatus()
              5000
            )
        else
          if @interval
            @$interval.cancel @interval
            @interval = null

        @getFiles() if 'completed' == val



    initialLoad: ->
      @getStatus()
      @getFiles()



    getStatus: ->
      @Api.sendGet('/export/status').then (data) =>
        @$scope.status = data.data?.status
        @$scope.offset = data.data?.offset



    getFiles: ->
      @Api.sendGet('/export/list').then (data) =>
        @$scope.files = data.data || []



  Admin_ExportCsv_Ctrl_ExportCsv.EXPORT_CTRL()