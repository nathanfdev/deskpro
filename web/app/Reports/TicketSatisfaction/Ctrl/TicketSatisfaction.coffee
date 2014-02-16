define [
	'Reports/Main/Ctrl/Base',
	'moment',
], (
	ReportsBaseCtrl,
	moment,
) ->
	class Reports_TicketSatisfaction_Ctrl_TicketSatisfaction extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_TicketSatisfaction_Ctrl_TicketSatisfaction'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = ['Api', '$sce']


		###
		# Initializing..
		###
		init: ->
			@html = ''
			@page_nums = [1]
			@num_pages = 0
			@page = 1


		###
		# Just doing all the necessary AJAX calls here
		###
		initialLoad: ->
			return @loadResults()


		###
		# This method updates current parameters that are used for sending request to API
		###
		updateFilter: ->
			@loadResults()


		###
		# Loading the results of sending request to API
		###
		loadResults: ->
			@startSpinner('loading_list_results')

			promise = @Api.sendGet("/reports/ticket-satisfaction/" + @page).then((res) =>
				@html = @$sce.trustAsHtml(res.data.html)

				@page = res.data.page
				@num_pages = res.data.num_pages

				@page_nums = []

				for i in [0...@num_pages]
					@page_nums.push(i + 1)

				@stopSpinner('loading_list_results', true)
			)

			return promise


		###
		# This is executed after we changed the current page
		###
		changePage: ->
			@loadResults()


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