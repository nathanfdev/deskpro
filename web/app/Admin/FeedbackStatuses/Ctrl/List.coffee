define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_FeedbackStatuses_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_List'
		@CTRL_AS = 'FeedbackStatusesList'
		@DEPS    = ['$rootScope', '$scope', 'em', 'Api', '$state', 'Growl']
		@CTRL_TYPE = 'list'

		init: ->
			@feedback_statuses_count = 0;
			@feedback_stasus_settings = {}

 Admin_FeedbackStatuses_Ctrl_List.EXPORT_CTRL()