define ['Admin/Labels/Base/Ctrl/List'], (Admin_Labels_Base_Ctrl_List) ->
	class Admin_Labels_Person_Ctrl_List extends Admin_Labels_Base_Ctrl_List
		@CTRL_ID = 'Admin_Labels_Person_Ctrl_List'
		@CTRL_AS = 'LabelsList'

		type: -> 'people'

	Admin_Labels_Person_Ctrl_List.EXPORT_CTRL()