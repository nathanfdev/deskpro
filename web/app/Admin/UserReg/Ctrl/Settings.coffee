define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_UserReg_Ctrl_Settings extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_UserReg_Ctrl_Settings'
		@CTRL_AS   = 'PageCtrl'
		@DEPS      = []

		init: ->
			@settings = null

		initialLoad: ->
			return

		isDirtyState: ->
			if not @settings then return false
			if not angular.equals(@settings, @$scope.settings)
				return true
			else
				return false

		save: ->
			postData = {

			}

			@startSpinner('saving')
			promise = @Api.sendPostJson('/registration_settings', postData).success( =>
				@settings = angular.copy(@$scope.settings)

				@stopSpinner('saving').then(=>
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			).error( (info, code) =>
				@stopSpinner('saving', true)
				@applyErrorResponseToView(info)
			)

	Admin_UserReg_Ctrl_Settings.EXPORT_CTRL()