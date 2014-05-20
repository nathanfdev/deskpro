define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_NewsSettings_Ctrl_NewsSettings extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_NewsSettings_Ctrl_NewsSettings'
		@CTRL_AS = 'Ctrl'
		@DEPS    = []

		initialLoad: ->
			@Api.sendGet('/settings/portal/news').then( (res) =>
				@$scope.settings = res.data.settings
			)

		save: ->
			postData = {
				settings: @$scope.settings
			}

			@startSpinner('saving')
			@Api.sendPostJson('/settings/portal/news', postData).then( =>
				@stopSpinner('saving')
			)

	Admin_NewsSettings_Ctrl_NewsSettings.EXPORT_CTRL()