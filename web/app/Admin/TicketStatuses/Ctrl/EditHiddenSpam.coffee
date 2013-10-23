define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketStatuses_Ctrl_EditHiddenSpam extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenSpam'
		@CTRL_AS = 'TicketStatusEdit'
		@DEPS = []
		@CTRL_TYPE = 'page'

		init: ->
			@$scope.settings = {
				auto_purge_time: 604800
			}
			return

		initialLoad: ->
			promise = @Api.sendGet("/ticket_statuses/spam").success( (data) =>
				@$scope.settings.auto_purge_time = data.spam_info.auto_purge_time
			);

			return promise

		saveSettings: ->
			@startSpinner('saving_settings')
			promise = @Api.sendPostJson('/ticket_statuses/spam/settings', @$scope.settings).then( =>
				@stopSpinner('saving_settings')
			)

			return promise

	Admin_TicketStatuses_Ctrl_EditHiddenSpam.EXPORT_CTRL()