define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Settings_Ctrl_ServerSettings extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Settings_Ctrl_ServerSettings'
		@CTRL_AS = 'Settings'
		@DEPS = []

		init: ->
			@settings = null

		initialLoad: ->
			data_promise = @Api.sendDataGet({
				'settings': '/server_settings'
			}).then((res) =>
				@$scope.settings = res.data.settings.server_settings
				@settings = angular.copy(@$scope.settings)
			)

			return @$q.all([data_promise])

		isDirtyState: ->
			if not @settings then return false
			if not angular.equals(@settings, @$scope.settings)
				return true
			else
				return false

		save: ->
			postData = {
				server_settings: @$scope.settings
			}

			@startSpinner('saving')
			promise = @Api.sendPostJson('/server_settings', postData).success(=>
				@settings = angular.copy(@$scope.settings)

				@stopSpinner('saving').then(=>
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			).error((info, code) =>
				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

	Admin_Settings_Ctrl_ServerSettings.EXPORT_CTRL()