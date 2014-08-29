define ['Admin/Labels/Base/Ctrl/List'], (Admin_Labels_Base_Ctrl_List) ->
	class Admin_Labels_Feedback_Ctrl_List extends Admin_Labels_Base_Ctrl_List
		@CTRL_ID = 'Admin_Labels_Feedback_Ctrl_List'
		@CTRL_AS = 'LabelsList'

		type: -> 'feedback'

	Admin_Labels_Feedback_Ctrl_List.EXPORT_CTRL()