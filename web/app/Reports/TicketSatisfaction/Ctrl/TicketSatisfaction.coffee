define [
	'Reports/Main/Ctrl/Base',
	'moment',
], (
	ReportsBaseCtrl,
	moment,
) ->
	class Reports_TicketSatisfaction_Ctrl_TicketSatisfaction extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_TicketSatisfaction_Ctrl_TicketSatisfaction'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = ['Api', '$sce']


		###
		# Initializing..
		###
		init: ->
			@feed_html = ''
			@summary_html = ''
			@page_nums = [1]
			@num_pages = 0
			@page = 1
			@date = moment(@date).format("YYYY-MM")


		###
		# Just doing all the necessary AJAX calls here
		###
		initialLoad: ->
			return @loadFeedResults()


		###
 	# Switching to feed tab
 	###
		switchToFeed: ->
			@loadFeedResults()


		###
 	# Switching to summary tab
 	###
		switchToSummary: ->
			@loadSummaryResults()


		###
		# Loading the results of sending request to API
		###
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


		###
		# Loading the results of sending request to API
		###
		loadSummaryResults: ->
			@startSpinner('loading_summary_results')

			promise = @Api.sendGet("/reports/ticket-satisfaction/summary/" + @date).then((res) =>
				@summary_html = @$sce.trustAsHtml(res.data.html)
				@stopSpinner('loading_summary_results', true)
			)

			return promise


		###
		# This is executed after we changed the current page
		###
		changePage: ->
			@loadFeedResults()


		###
		#
		###
		goPrevPage: ->
			@page--
			@changePage()


		###
		#
		###
		goNextPage: ->
			@page++
			@changePage()


		Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.EXPORT_CTRL()