define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_DownloadsSettings_Ctrl_DownloadsSettings extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_DownloadsSettings_Ctrl_DownloadsSettings'
		@CTRL_AS = 'Ctrl'
		@DEPS    = []

		initialLoad: ->
			@Api.sendGet('/settings/portal/downloads').then( (res) =>
				@$scope.settings = res.data.settings
			)

		save: ->
			postData = {
				settings: @$scope.settings
			}

			@startSpinner('saving')
			@Api.sendPostJson('/settings/portal/downloads', postData).then( =>
				@stopSpinner('saving')
			)


	Admin_DownloadsSettings_Ctrl_DownloadsSettings.EXPORT_CTRL()