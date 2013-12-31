define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_NewsSettings_Ctrl_NewsSettings extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_NewsSettings_Ctrl_NewsSettings'
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

			data_promise = @Api.sendGet('/enable_settings/app_news').then( (res) =>
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

			@Api.sendPost('/enable_settings/app_news/toggle/' + val)


	Admin_NewsSettings_Ctrl_NewsSettings.EXPORT_CTRL()