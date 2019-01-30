define [
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/LocalStore',
  'moment'
], (
  Admin_Ctrl_Base,
  LocalStore,
  moment) ->
  class Admin_EmailStatus_Ctrl_SourceList extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_EmailStatus_Ctrl_SourceList'
    @CTRL_AS = 'ListCtrl'

    init: ->
      @storeFilterId = Admin_EmailStatus_Ctrl_SourceList.CTRL_ID+'.filter'
      @filter = {
        account: "0",
        page: 1
      }
      @results = []
      @num_results = 0
      @num_pages = 0
      @page_nums = [1]
      @filter_date_mode = "none"
      @page = 1
      @massActionsOp = "reprocess"

      if LocalStore.has(@storeFilterId)
        @filter = LocalStore.getObject(@storeFilterId, @filter)
        @filter.page = 1
        @$scope.filter_open = true

      @$scope.$watch('ListCtrl.page', (newVal, oldVal) =>
        if parseInt(newVal) == parseInt(oldVal)
          return
        if isNaN(parseInt(newVal))
          return

        @changePage()
      )

      @$scope.showStatusHelp = =>
        modalInstance = @$modal.open({
          templateUrl: @getTemplatePath('EmailStatus/emailsource-status-code-modal.html'),
          controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
            $scope.dismiss = ->
              $modalInstance.dismiss()
          ]
        })

    initialLoad: ->
      p1 = @loadResults()
      p2 = @Api.sendGet('/email_accounts').success( (data) =>
        @$scope.email_accounts = data.email_accounts
      )
      @Api.sendGet('/email_status/stats').success( (data) =>
        @$scope.counts = {
          status: data.by_status || {},
          account: data.by_account || {}
        }
      )

      return @$q.all([p1, p2])

    changePage: ->
      if @filter.page == @page
        return

      @filter.page = @page
      @loadResults(true)

    clearFilter: ->
      @filter = {
        account: "0",
        page: 1
      }
      @$scope.filter_open = false
      @updateFilter(true)
      LocalStore.remove(@storeFilterId)

    updateFilter: (skipSave) ->
      @page = 1
      @filter.page = @page

      @filter.date_start = null
      @filter.date_end = null
      if @filter_date_mode and @filter_date_mode != 'none'
        if @filter_date1 and (@filter_date_mode == 'between' || @filter_date_mode == 'after')
          @filter.date_start = moment(@filter_date1).format("YYYY-MM-DD")
        if @filter_date2 and (@filter_date_mode == 'between' || @filter_date_mode == 'before')
          @filter.date_end = moment(@filter_date2).format("YYYY-MM-DD")

      if not skipSave
        LocalStore.setObject(@storeFilterId, @filter)

      @loadResults()

    loadResults: (fallbackPrevPage) ->
      @startSpinner('loading_page')
      @results = []
      promise = @Api.sendGet('/email_status/sources', {filter: @filter}).success( (data) =>
        @stopSpinner('loading_page', true)
        @results     = data.email_sources
        @filter.page = data.page
        @page        = data.page
        @num_pages   = data.num_pages
        @num_results = data.count
        @massActions = {}
        @massActionsAll = false
        @massActionsLoading = false

        @page_nums = []
        for i in [1..@num_pages]
          @page_nums.push(i)

        if fallbackPrevPage and !@results.length and data.page > 1
          @filter.page = data.page - 1;
          @loadResults()
      )

      return promise

    toggleMassActions: ->
      @massActions = {}
      if @massActionsAll
        for r in @results
          @massActions[r.id] = true

    hasAnyMassActions: ->
      for r in @results
        return true if @massActions[r.id]
      return false

    performMassActions: ->
      url = "/email_status/sources/mass-actions/#{@massActionsOp}"
      @massActionsLoading = true

      ids = []
      for r in @results
        if @massActions[r.id] then ids.push(r.id)

      @Api.sendPostJson(url, { ids: ids }).then(=>
        @Growl.success(@getRegisteredMessage("#{@massActionsOp}_done"))
        @loadResults(true)
      )

    goPrevPage: ->
      @page--

    goNextPage: ->
      @page++

  Admin_EmailStatus_Ctrl_SourceList.EXPORT_CTRL()