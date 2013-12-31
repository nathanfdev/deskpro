define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_FeedbackSettings_Ctrl_FeedbackSettings extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackSettings_Ctrl_FeedbackSettings'
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

			data_promise = @Api.sendGet('/enable_settings/app_feedback').then( (res) =>
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

			@Api.sendPost('/enable_settings/app_feedback/toggle/' + val)


	Admin_FeedbackSettings_Ctrl_FeedbackSettings.EXPORT_CTRL()