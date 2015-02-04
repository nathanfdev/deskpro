define [
  'DeskPRO/Main/Ctrl/Base',
  'moment',
], (
  ReportsBaseCtrl,
  moment,
) ->
  class Reports_TicketSatisfaction_Ctrl_TicketSatisfaction extends ReportsBaseCtrl
    @CTRL_ID   = 'Reports_TicketSatisfaction_Ctrl_TicketSatisfaction'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['Api', '$sce']

    init: ->
      @feed_html = ''
      @summary_html = ''
      @page_nums = [1]
      @num_pages = 0
      @page = 1
      @$scope.view_date = moment(@date).format("YYYY-MM")
      @$scope.mode = 'feed'

    initialLoad: ->
      return @loadFeedResults()

    switchToFeed: ->
      @$scope.mode = 'feed'
      @loadFeedResults()

    switchToSummary: ->
      @$scope.mode = 'summary'
      @loadSummaryResults()

    loadFeedResults: ->
      @startSpinner('loading_feed_results')

      promise = @Api.sendGet("/reports/ticket-satisfaction/" + @page).then((res) =>
        @feed_html = @$sce.trustAsHtml(res.data.html)

        @page = res.data.page || 1
        @num_pages = res.data.num_pages || 1

        @page_nums = []

        for i in [0...@num_pages]
          @page_nums.push(i + 1)

        @stopSpinner('loading_feed_results', true)
      )

      return promise

    loadSummaryResults: ->
      @startSpinner('loading_summary_results')

      promise = @Api.sendGet("/reports/ticket-satisfaction/summary/" + @$scope.view_date).then((res) =>
        @summary_html = @$sce.trustAsHtml(res.data.html)
        @stopSpinner('loading_summary_results', true)
      )

      return promise

    changePage: ->
      @loadFeedResults()

    goPrevPage: ->
      @page--
      @changePage()

    goNextPage: ->
      @page++
      @changePage()

    Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.EXPORT_CTRL()