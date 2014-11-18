define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ServerCron_Ctrl_Logs extends Admin_Ctrl_Base

    @CTRL_ID   = 'Admin_ServerCron_Ctrl_Logs'
    @CTRL_AS   = 'LogsCtrl'
    @DEPS      = []

    init: ->
      @server_cron_logs = null
      @page = 1
      @num_pages = 0
      @page_nums = [1]

      @filter = {
        job_id: '',
        priority: '',
        page: 1
      }

      @initializeScopeWatching()

    initialLoad: ->

      return @loadResults()

    ###
  #
  ###

    loadResults: ->

      @startSpinner('paginating_server_cron_logs')

      data_promise = @Api.sendGet('/server_cron/logs', {
        job_id: @filter.job_id,
        priority: @filter.priority,
        page: @filter.page
      }).then((res) =>

        @server_cron_logs = res.data.server_cron_logs

        @filter.page = res.data.server_cron_logs.page
        @page = res.data.server_cron_logs.page
        @num_pages = res.data.server_cron_logs.num_pages

        @page_nums = []

        for i in [0...@num_pages]
          @page_nums.push(i + 1)

        @stopSpinner('paginating_server_cron_logs', true)
      )

      return @$q.all([data_promise])

    updateFilter: ->

      @loadResults()

    ###
    # Show the clear dlg
    ###

    startClearAll: ->

      inst = @$modal.open({
        templateUrl: @getTemplatePath('Server/server-cron-delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close()

          $scope.dismiss = ->
            $modalInstance.dismiss()
        ]
      });

      inst.result.then(() =>
        @clearAll()
      )

    ###
    # Actually do the clear
    ###

    clearAll: ->

      @Api.sendDelete('/server_cron/logs').success( =>

        @server_cron_logs = null
      )

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
    # This is executed after we chnaged the current page
    ###

    changePageCallback: ->

      @filter.page = @page
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

  Admin_ServerCron_Ctrl_Logs.EXPORT_CTRL()