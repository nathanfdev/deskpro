define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_KbSettings_Ctrl_KbSettings extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_KbSettings_Ctrl_KbSettings'
		@CTRL_AS = 'Ctrl'
		@DEPS    = []

		###
 	#
		###

		init: ->

			return

		###
 	#
		###

		initialLoad: ->

			data_promise = @Api.sendGet('/enable_settings/app_kb').then( (res) =>
				@$scope.status = res.data.status
			)

			return @$q.all([data_promise])

		###
		#
		###

		toggle: () ->

			if @$scope.status
				val = '1'
			else
				val = '0'

			@Api.sendPost('/enable_settings/app_kb/toggle/' + val)


	Admin_KbSettings_Ctrl_KbSettings.EXPORT_CTRL()