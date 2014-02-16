define [
	'Reports/Main/Ctrl/Base',
	'moment',
], (
	ReportsBaseCtrl,
	moment,
) ->
	class Reports_TicketSatisfaction_Ctrl_List extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_TicketSatisfaction_Ctrl_List'
		@CTRL_AS   = 'List'
		@DEPS      = ['Api', '$sce']


		###
		# Initializing..
		###
		init: ->
			@html = ''
			@filter = {}
			@filter.page = 0


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

			promise = @Api.sendGet("/reports/ticket-satisfaction/" + @filter.page).then((res) =>
				@html = @$sce.trustAsHtml(res.data.html)

				@stopSpinner('loading_list_results', true)
			)

			return promise


		Reports_TicketSatisfaction_Ctrl_List.EXPORT_CTRL()