define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_FeedbackStatuses_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_Edit'
		@CTRL_AS = 'FeedbackStatusesEdit'
		@DEPS    = ['Api', 'Growl', 'FeedbackStatusesData', '$stateParams', '$modal']
		@CTRL_TYPE = 'page'

		init: ->

			@feedback_status = {}

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
			else
				is_new = true

			@stopSpinner('saving_feedback_status', true).then(=>
				@Growl.success(@getRegisteredMessage('saved_feedback_status'))
			)

			@FeedbackStatusesData.updateModel(@feedback_status)

			@skipDirtyState()

			if is_new
				@$state.go('portal.feedback_statuses.gocreate')
			else
				@$state.go('portal.feedback_statuses')

			return

	Admin_FeedbackStatuses_Ctrl_Edit.EXPORT_CTRL()