define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_FeedbackTypes_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackTypes_Ctrl_Edit'
		@CTRL_AS = 'FeedbackTypesEdit'
		@DEPS    = ['Api', 'Growl', 'FeedbackTypesData', '$stateParams', '$modal']
		@CTRL_TYPE = 'page'

		init: ->

			@feedback_type = {}

			return

		initialLoad: ->

			if not @$stateParams.id

				return

			else

				data_promise = @Api.sendGet('/feedback_types/' + @$stateParams.id).then((result) =>
					@feedback_type = result.data.feedback_type
				)

				return @$q.all([data_promise])

		###
			# Saves the current form
			#
			# @return {promise}
		###
		saveFeedbackType: ->

			if not @$scope.form_props.$valid
				return

			@startSpinner('saving_feedback_type')

			if @feedback_type.id
				is_new = false
				promise = @Api.sendPostJson('/feedback_types/' + @feedback_type.id, {feedback_type: @feedback_type})
			else
				is_new = true
				promise = @Api.sendPutJson('/feedback_types', {feedback_type: @feedback_type})

			promise.success((result) =>

				@feedback_type.id = result.id

				@stopSpinner('saving_feedback_type', true).then(=>
					@Growl.success(@getRegisteredMessage('saved_feedback_type'))
				)

				@FeedbackTypesData.updateModel(@feedback_type)

				@skipDirtyState()

				if is_new
					@$state.go('portal.feedback_types.gocreate')
				else
					@$state.go('portal.feedback_types')
			)
			promise.error((info, code) =>
				@stopSpinner('saving_feedback_type', true)
				@applyErrorResponseToView(info)
			)

			return promise

	Admin_FeedbackTypes_Ctrl_Edit.EXPORT_CTRL()