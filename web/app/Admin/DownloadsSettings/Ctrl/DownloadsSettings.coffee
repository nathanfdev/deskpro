define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_DownloadsSettings_Ctrl_DownloadsSettings extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_DownloadsSettings_Ctrl_DownloadsSettings'
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

			data_promise = @Api.sendGet('/enable_settings/app_downloads').then( (res) =>
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

			@Api.sendPost('/enable_settings/app_downloads/toggle/' + val)


	Admin_DownloadsSettings_Ctrl_DownloadsSettings.EXPORT_CTRL()