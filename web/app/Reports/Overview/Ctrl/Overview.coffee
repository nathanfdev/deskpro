define [
	'Reports/Main/Ctrl/Base'
], (
	ReportsBaseCtrl
) ->
	class Reports_Overview_Ctrl_Overview extends ReportsBaseCtrl
		@CTRL_ID   = 'Reports_Overview_Ctrl_Overview'
		@CTRL_AS   = 'Overview'
		@DEPS      = []

		init: ->

			return

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				tickets_status: "/reports/overview/data/tickets_status",
			}).then( (res) =>
				@$scope.tickets_status = res.data.tickets_status

				@setPercentagesForTicketsStatuses()
			)

			@$q.all([data_promise])

		###
		#
		###

		setPercentagesForTicketsStatuses: ->
			for key, value of @$scope.tickets_status.titles
				if @$scope.tickets_status.values[key]
					console.log @$scope.tickets_status.values[key]

	Reports_Overview_Ctrl_Overview.EXPORT_CTRL()