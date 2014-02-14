define [
	'Reports/Main/Ctrl/Base',
	'DeskPRO/Util/Util',
], (
	ReportsBaseCtrl,
	Util,
) ->
	class Reports_AgentActivity_Ctrl_AgentActivity extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_AgentActivity_Ctrl_AgentActivity'
		@CTRL_AS   = 'AgentActivity'
		@DEPS      = ['Api', '$sce']


		###
		#
		###
		init: ->
			@html = ''

			return


		###
		# Just doing all the necessary AJAX calls here
		###
		initialLoad: ->
			data_promise = @Api.sendGet("/reports/agent-activity").then( (res) =>
				@html = @$sce.trustAsHtml(res.data.html)
			)

			@$q.all([data_promise])


	Reports_AgentActivity_Ctrl_AgentActivity.EXPORT_CTRL()