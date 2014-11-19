define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_ServerReqs_Ctrl_ServerReqs extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_ServerReqs_Ctrl_ServerReqs'
    @CTRL_AS   = 'ServerReqs'
    @DEPS      = []

    init: ->
      @$scope.server_reqs = null

    initialLoad: ->
      data_promise = @Api.sendGet('/server_reqs').then( (res) =>

        @$scope.server_reqs = res.data.server_reqs
      )

      return @$q.all([data_promise])

  Admin_ServerReqs_Ctrl_ServerReqs.EXPORT_CTRL()