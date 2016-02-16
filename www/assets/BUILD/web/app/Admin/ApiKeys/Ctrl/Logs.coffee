define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ApiKeys_Ctrl_Logs extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ApiKeys_Ctrl_Logs'
    @CTRL_AS = 'LogsCtrl'

    init: ->
      @service = @DataService.get 'ApiLogs'
      @list = []
      @filtration = @service.getFiltration()
      @modes = ['session', 'key', 'token']
      @methods = ['GET', 'POST', 'PUT', 'DELETE'] # I know, I know, but we use only listed

    ###
    # Loads the list
    ###
    initialLoad: ->
      @service.loadList(null, {page: 1}).then( (data) =>
        @list = data
        @pagination = @service.getPagination()
        @initializeScopeWatching()
      )

    initializeScopeWatching: ->

      @$scope.$watch('LogsCtrl.pagination', (newVal, oldVal) =>

        old_page = parseInt(oldVal.page)
        new_page = parseInt(newVal.page)

        if old_page == new_page or isNaN(new_page)
          return undefined

        @service.refreshList().then( (data) =>
          @list = data
          @pagination = @service.getPagination()
        )
      , true)

  Admin_ApiKeys_Ctrl_Logs.EXPORT_CTRL()