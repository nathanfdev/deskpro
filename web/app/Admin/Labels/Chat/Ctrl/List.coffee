define ['Admin/Labels/Base/Ctrl/List'], (Admin_Labels_Base_Ctrl_List) ->
	class Admin_Labels_Chat_Ctrl_List extends Admin_Labels_Base_Ctrl_List
		@CTRL_ID = 'Admin_Labels_Chat_Ctrl_List'
		@CTRL_AS = 'LabelsList'
		@DEPS = ['em', '$rootScope', 'LabelManager']

		init: ->
			super()
			@api_endpoint = '/chat_labels'
			@ng_route     = 'chat.labels'
			@typename     = 'labels_chat'

	Admin_Labels_Chat_Ctrl_List.EXPORT_CTRL()