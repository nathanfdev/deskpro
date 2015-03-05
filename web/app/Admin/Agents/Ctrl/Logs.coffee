define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Agents_Ctrl_Logs extends Admin_Ctrl_Base

    @CTRL_ID   = 'Admin_Agents_Ctrl_Logs'
    @CTRL_AS   = 'LogsCtrl'
    @DEPS      = []

    init: ->
      @login_logs = null
      @page = 1
      @num_pages = 0
      @page_nums = [1]

      @initializeScopeWatching()

    initialLoad: ->

      return @loadResults()

    ###
  #
  ###

    loadResults: ->

      @startSpinner('paginating_login_logs')

      data_promise = @Api.sendGet('/login_logs', {
        page: @page
      }).then((res) =>

        @login_logs = res.data.login_logs
        @num_pages = res.data.login_logs.num_pages

        @page_nums = []

        for i in [0...@num_pages]
          @page_nums.push(i + 1)

        @stopSpinner('paginating_login_logs', true)
      )

      return @$q.all([data_promise])

    ###
  #
  ###

    updateFilter: ->

      @loadResults()

    ###
    # Here we watching scope 'page' variable in order to load new page of results
    ###

    initializeScopeWatching: ->

      @$scope.$watch('LogsCtrl.page', (newVal, oldVal) =>

        if parseInt(newVal) == parseInt(oldVal)
          return undefined

        if isNaN(parseInt(newVal))
          return undefined

        @changePageCallback()
      )

    ###
    # This is executed after we changed the current page
    ###

    changePageCallback: ->

      @loadResults()

    ###
  #
    ###

    goPrevPage: ->

      @page--

    ###
    #
    ###

    goNextPage: ->

      @page++

  Admin_Agents_Ctrl_Logs.EXPORT_CTRL()