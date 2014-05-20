define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_FeedbackStatuses_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_Edit'
		@CTRL_AS = 'FeedbackStatusesEdit'
		@DEPS    = ['Api', 'Growl', 'FeedbackStatusesData', '$stateParams', '$modal']

		init: ->

			@feedback_status = {}

			# @$stateParams.type will be defined in case of creation of new feedback status

			@statusType = @$stateParams.type

			if @statusType
				@feedback_status.status_type = @statusType

			return

		initialLoad: ->

			if not @$stateParams.id

				return

			else

				data_promise = @Api.sendGet('/feedback_statuses/' + @$stateParams.id).then((result) =>
					@feedback_status = result.data.feedback_status
				)

				return @$q.all([data_promise])

		###
			# Saves the current form
			#
			# @return {promise}
		###
		saveFeedbackStatus: ->

			if not @$scope.form_props.$valid
				return

			@startSpinner('saving_feedback_status')

			if @feedback_status.id
				is_new = false
				promise = @Api.sendPostJson('/feedback_statuses/' + @feedback_status.id, {feedback_status: @feedback_status})
			else
				is_new = true
				promise = @Api.sendPutJson('/feedback_statuses', {feedback_status: @feedback_status})

			promise.success((result) =>

				@feedback_status.id = result.id

				@stopSpinner('saving_feedback_status', true).then(=>
					@Growl.success(@getRegisteredMessage('saved_feedback_status'))
				)

				@FeedbackStatusesData.updateModel(@feedback_status)

				@skipDirtyState()

				if is_new
					@$state.go('portal.feedback_statuses.gocreate', {type: @statusType})
				else
					@$state.go('portal.feedback_statuses')
			)
			promise.error((info, code) =>
				@stopSpinner('saving_feedback_status', true)
				@applyErrorResponseToView(info)
			)

			return promise

	Admin_FeedbackStatuses_Ctrl_Edit.EXPORT_CTRL()