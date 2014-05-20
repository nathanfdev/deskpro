define [
	'Reports/Main/Ctrl/Base',
	'moment',
], (
	ReportsBaseCtrl,
	moment,
) ->
	class Reports_AgentHours_Ctrl_AgentHours extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_AgentHours_Ctrl_AgentHours'
		@CTRL_AS   = 'AgentHours'
		@DEPS      = ['Api', '$sce']


		###
		# Initializing..
		###
		init: ->
			@html = ''
			@date1 = new Date()
			@date2 = new Date()
			@filter = {}
			@filter.date1 = moment(@date).format("YYYY-MM-DD")
			@filter.date2 = moment(@date).format("YYYY-MM-DD")


		###
		# Just doing all the necessary AJAX calls here
		###
		initialLoad: ->
			return @loadResults()


		###
		# This method updates current parameters that are used for sending request to API
		###
		updateFilter: ->
			@filter.date1 = moment(@date1).format("YYYY-MM-DD")
			@filter.date2 = moment(@date2).format("YYYY-MM-DD")
			@loadResults()


		###
		# Loading the results of sending request to API
		###
		loadResults: ->
			@startSpinner('loading_results')

			promise = @Api.sendGet("/reports/agent-hours/" + @filter.date1 + "/" + @filter.date2).then((res) =>
				@html = @$sce.trustAsHtml(res.data.html)

				@stopSpinner('loading_results', true)
			)

			return promise


	Reports_AgentHours_Ctrl_AgentHours.EXPORT_CTRL()