define ['Admin/Labels/Base/Ctrl/List'], (Admin_Labels_Base_Ctrl_List) ->
	class Admin_Labels_Feedback_Ctrl_List extends Admin_Labels_Base_Ctrl_List
		@CTRL_ID = 'Admin_Labels_Feedback_Ctrl_List'
		@CTRL_AS = 'LabelsList'
		@DEPS = ['em', '$rootScope', 'LabelManager']

		init: ->
			super()
			@api_endpoint = '/feedback_labels'
			@ng_route     = 'portal.feedback_labels'
			@typename     = 'labels_feedback'

	Admin_Labels_Feedback_Ctrl_List.EXPORT_CTRL()