define ['Admin/Labels/Base/Ctrl/List'], (Admin_Labels_Base_Ctrl_List) ->
	class Admin_Labels_Person_Ctrl_List extends Admin_Labels_Base_Ctrl_List
		@CTRL_ID = 'Admin_Labels_Person_Ctrl_List'
		@DEPS = ['em', '$rootScope', 'LabelManager']
		@CTRL_AS = 'LabelsList'

		init: ->
			super()
			@api_endpoint = '/person_labels'
			@ng_route     = 'crm.user_labels'
			@typename     = 'labels_people'

	Admin_Labels_Person_Ctrl_List.EXPORT_CTRL()