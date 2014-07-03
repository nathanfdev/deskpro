define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Cloud_Settings_Ctrl_CustomDomain extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Cloud_Settings_Ctrl_CustomDomain'
		@CTRL_AS   = 'Ctrl'

		initialLoad: ->
			@Api.sendGet('/settings/cloud/url-settings').then( (res) =>
				@$scope.form = res.data.settings
			)

		save: ->
			@startSpinner('saving')
			postData = {
				settings: @$scope.form
			}
			@Api.sendPostJson('/settings/cloud/url-settings', postData).then(->
				@stopSpinner('saving').then(=>
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			)

	Admin_Cloud_Settings_Ctrl_CustomDomain.EXPORT_CTRL()