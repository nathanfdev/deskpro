define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Server_Ctrl_ServerEnc extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Server_Ctrl_ServerEnc'
    @CTRL_AS   = 'ServerEnc'
    @DEPS      = []

    init: ->
      return

    loadStatus: ->
      return @Api.sendGet('/server/encryption/status').then((res) =>
        @$scope.status = res.data;
      );

    reloadStatus: ->
      @startSpinner('saving')
      @loadStatus().then(=>
        @stopSpinner('saving', true)
      )

    enable: ->
      @startSpinner('saving')
      @$scope.error_code = null
      @Api.sendPost('/server/encryption/enable').success((res) =>
        @loadStatus().then(=>
          @stopSpinner('saving', true)
        )
      ).error((info, code) =>
        console.log(info)
      )

    disable: ->
      @startSpinner('saving')
      @$scope.error_code = null
      @Api.sendPost('/server/encryption/disable').success((res) =>
        @loadStatus().then(=>
          @stopSpinner('saving', true)
        )
      ).error((info, code) =>
        console.log(info)
      )

    initialLoad: ->
      return @loadStatus()

  Admin_Server_Ctrl_ServerEnc.EXPORT_CTRL()