define ['Admin/Labels/Base/Ctrl/Edit'], (Admin_Labels_Base_Ctrl_Edit) ->
	class Admin_Labels_Org_Ctrl_Edit extends Admin_Labels_Base_Ctrl_Edit
		@CTRL_ID = 'Admin_Labels_Org_Ctrl_Edit'
		@CTRL_AS = 'LabelsEdit'

		type: -> 'organizations'

	Admin_Labels_Org_Ctrl_Edit.EXPORT_CTRL()