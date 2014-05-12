define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_TicketStatuses_Ctrl_EditClosed extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditClosed'
		@CTRL_AS = 'TicketStatusEdit'
		@DEPS = []

		init: ->
			@$scope.getCount = => @$scope.$parent.TicketStatusesList?.getStatusCount('closed')
			@$scope.settings = {
				enabled: false,
				auto_archive_time: 2419200
			}
			return

		initialLoad: ->
			promise = @Api.sendGet("/ticket_statuses/closed").success( (data) =>
				@$scope.settings.enabled = data.closed_info.enabled
				@$scope.settings.auto_archive_time = parseInt(data.closed_info.auto_archive_time)
			);

			return promise

		saveSettings: ->
			@startSpinner('saving_settings')
			promise = @Api.sendPostJson('/ticket_statuses/closed/settings', @$scope.settings).then( =>
				@stopSpinner('saving_settings')
			)

			return promise

	Admin_TicketStatuses_Ctrl_EditClosed.EXPORT_CTRL()