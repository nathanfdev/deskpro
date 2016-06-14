define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Settings_Ctrl_ResetDemo extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_ResetDemo'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$interval']

    init: ->
      @$scope.status = {}
      @$scope.$on '$destroy', => @interval && @$interval.cancel(@interval)



    initialLoad: ->
      @Api.sendGet('/reset-demo/status').then (res) => @status(res.data)



    status: (data) ->
      @$scope.status = data
      if data.waiting && !@interval
        refresh = => @Api.sendGet('/reset-demo/status').then (res) => @status(res.data)
        @interval = @$interval refresh, 5000
      if !data.waiting && @interval
        @$interval.cancel(@interval)
        @interval = null



    save: ->
      return if !@$scope.form_props || @$scope.form_props.$invalid
      post = @$scope.form
      @$scope.form = {}
      @Api.sendPostJson('/reset-demo', post).then (res) => @status(res.data)



  Admin_Settings_Ctrl_ResetDemo.EXPORT_CTRL()