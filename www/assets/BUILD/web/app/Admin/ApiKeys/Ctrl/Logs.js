define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], (Admin_Ctrl_Base, Arrays) ->
  class Admin_ApiKeys_Ctrl_Logs extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ApiKeys_Ctrl_Logs'
    @CTRL_AS = 'LogsCtrl'

    init: ->
      @isCloud = window.DP_IS_CLOUD;
      @service = @DataService.get 'ApiLogs'
      @list = []
      @filtration = @service.getFiltration()
      @methods = ['GET', 'POST', 'PUT', 'DELETE'] # I know, I know, but we use only listed
      @options = {
        enabled: false
      }

    ###
    # Loads the list
    ###
    initialLoad: ->
      @service.getOptions().then((data) =>
        @options = data
      )

      @service.loadList(null, {page: 1}).then( (data) =>
        @list = data
        @pagination = @service.getPagination()
        @initializeScopeWatching()
      )

    toggleLogs: ->
      @options.enabled = !@options.enabled

    updateOptions: ->
      @service.updateOptions(@options).then(
        () =>
          @Growl.success 'Successfully saved your new settings'
          @service.getOptions().then((data) =>
            @options = data
          )
        =>
          @Growl.error 'Error while saving'
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

    refreshList: ->
      @startSpinner 'loading'
      @service.refreshList().then( () => @stopSpinner 'loading')

  Admin_ApiKeys_Ctrl_Logs.EXPORT_CTRL()