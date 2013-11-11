define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketStatuses_Ctrl_EditHiddenDeleted extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenDeleted'
		@CTRL_AS = 'TicketStatusEdit'
		@DEPS = []

		init: ->
			@$scope.settings = {
				auto_purge_time: 604800
			}
			return

		initialLoad: ->
			promise = @Api.sendGet("/ticket_statuses/deleted").success( (data) =>
				@$scope.settings.auto_purge_time = data.deleted_info.auto_purge_time
			)

			return promise

		saveSettings: ->
			@startSpinner('saving_settings')
			promise = @Api.sendPostJson('/ticket_statuses/deleted/settings', @$scope.settings).then( =>
				@stopSpinner('saving_settings')
			)

			return promise

	Admin_TicketStatuses_Ctrl_EditHiddenDeleted.EXPORT_CTRL()