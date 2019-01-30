define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ServerTaskQueue_Ctrl_ServerTaskQueue extends Admin_Ctrl_Base

    @CTRL_ID   = 'Admin_ServerTaskQueue_Ctrl_ServerTaskQueue'
    @CTRL_AS   = 'ServerTaskQueue'
    @DEPS      = []

    init: ->
      @$scope.server_task_queue = null

    initialLoad: ->
      data_promise = @Api.sendGet('/server_task_queue').then( (res) =>

        @$scope.server_task_queue = res.data.server_task_queue
      )

      return @$q.all([data_promise])

  Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.EXPORT_CTRL()