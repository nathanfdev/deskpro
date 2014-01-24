define [
	'Reports/Main/Ctrl/Base'
], (
	ReportsBaseCtrl
) ->
	class Reports_Overview_Ctrl_Overview extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Overview_Ctrl_Overview'
		@CTRL_AS   = 'Overview'
		@DEPS      = []


		###
		#
		###
		init: ->

			return


		###
		# Just doing all the necessary AJAX calls here
		###
		initialLoad: ->
			data_promise = @Api.sendDataGet({
				tickets_status:         "/reports/overview/data/tickets_status",
				tickets_awaiting_agent: "/reports/overview/data/tickets_awaiting_agent",
			}).then( (res) =>
				@$scope.tickets_status = res.data.tickets_status
				@$scope.tickets_awaiting_agent = res.data.tickets_awaiting_agent

				@setVariablesForTicketsStatuses()
				@setVariablesForTicketsAwaitingAgent()
			)

			@$q.all([data_promise])


		###
		# We need to display bar graphs - so let's pre-calculate some variables
		###
		setVariablesForTicketsStatuses: ->
			@$scope.tickets_status.stats = []
			denominator = @$scope.tickets_status.max || 1

			for key of @$scope.tickets_status.titles when @$scope.tickets_status.values[key]

				percentage = @$scope.tickets_status.values[key] / denominator * 100
				percentage = 1 if percentage < 1

				@$scope.tickets_status.stats.push({
					title: @$scope.tickets_status.titles[key]
					value: @$scope.tickets_status.values[key]
					left_percentage: percentage,
					right_percentage: 100 - percentage
				})

		###
		# We need to display bar graphs - so let's pre-calculate some variables
		###
		setVariablesForTicketsAwaitingAgent: ->
			@$scope.tickets_awaiting_agent.stats = []
			denominator = @$scope.tickets_awaiting_agent.max || 1

			for key of @$scope.tickets_awaiting_agent.titles when @$scope.tickets_awaiting_agent.values[key]

				percentage = @$scope.tickets_awaiting_agent.values[key] / denominator * 100
				percentage = 1 if percentage < 1

				@$scope.tickets_awaiting_agent.stats.push({
					title: @$scope.tickets_awaiting_agent.titles[key]
					value: @$scope.tickets_awaiting_agent.values[key]
					left_percentage: percentage,
					right_percentage: 100 - percentage
				})

	Reports_Overview_Ctrl_Overview.EXPORT_CTRL()