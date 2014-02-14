define [
	'Reports/Main/Ctrl/Base',
	'moment',
], (
	ReportsBaseCtrl,
	moment,
) ->
	class Reports_AgentActivity_Ctrl_AgentActivity extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_AgentActivity_Ctrl_AgentActivity'
		@CTRL_AS   = 'AgentActivity'
		@DEPS      = ['Api', '$sce']


		###
		# Initializing..
		###
		init: ->
			@html = ''
			@date = new Date()
			@filter = {}


		###
		# Just doing all the necessary AJAX calls here
		###
		initialLoad: ->
			return @loadResults()


		###
		# This method updates current parameters that are used for sending request to API
		###
		updateFilter: ->
			@filter.date = moment(@date).format("YYYY-MM-DD")
			@loadResults()


		###
		# Loading the results of sending request to API
		###
		loadResults: ->
			promise = @Api.sendGet("/reports/agent-activity/0/" + @filter.date).then((res) =>
				@html = @$sce.trustAsHtml(res.data.html)
			)

			return promise


	Reports_AgentActivity_Ctrl_AgentActivity.EXPORT_CTRL()