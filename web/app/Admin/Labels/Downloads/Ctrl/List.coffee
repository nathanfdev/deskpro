define ['Admin/Labels/Base/Ctrl/List'], (Admin_Labels_Base_Ctrl_List) ->
	class Admin_Labels_Downloads_Ctrl_List extends Admin_Labels_Base_Ctrl_List
		@CTRL_ID = 'Admin_Labels_Downloads_Ctrl_List'
		@CTRL_AS = 'LabelsList'
		@DEPS = ['em', '$rootScope', 'LabelManager']

		init: ->
			super()
			@api_endpoint = '/downloads_labels'
			@ng_route     = 'portal.downloads_labels'
			@typename     = 'labels_downloads'

	Admin_Labels_Downloads_Ctrl_List.EXPORT_CTRL()