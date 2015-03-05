define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Apps_Ctrl_Resync extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Apps_Ctrl_Resync'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = []

    init: ->
      @$scope.state = 'default'
      return

    initialLoad: ->
      return

    beginResync: ->
      @$scope.state = 'running'
      @Api.sendPost("/apps/resync-packages").then((result) =>
        @$scope.state = 'done'
        @$scope.log = result.data.log
      , (result) =>
        @$scope.state = 'done'
        @$scope.log = 'There was an error. Please try again.'
      )

  Admin_Apps_Ctrl_Resync.EXPORT_CTRL()