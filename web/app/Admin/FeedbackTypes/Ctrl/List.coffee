define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_FeedbackTypes_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackTypes_Ctrl_List'
		@CTRL_AS = 'FeedbackTypesList'
		@DEPS    = ['$rootScope', '$scope', 'FeedbackTypesData', 'em', 'Api', '$state', 'Growl']
		@CTRL_TYPE = 'list'

		init: ->

			@feedback_types = [];

		initialLoad: ->

			list_promise = @FeedbackTypesData.loadList().then( (recs) =>

				@feedback_types = recs.values()

				@addManagedListener(@FeedbackTypesData.recs, 'changed', =>

					@feedback_types = @FeedbackTypesData.recs.values()
					@ngApply()
				)
			)

			return @$q.all([list_promise])

	Admin_FeedbackTypes_Ctrl_List.EXPORT_CTRL()