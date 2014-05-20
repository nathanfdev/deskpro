define ['Admin/Labels/Base/Ctrl/List'], (Admin_Labels_Base_Ctrl_List) ->
	class Admin_Labels_News_Ctrl_List extends Admin_Labels_Base_Ctrl_List
		@CTRL_ID = 'Admin_Labels_News_Ctrl_List'
		@CTRL_AS = 'LabelsList'
		@DEPS = ['em', '$rootScope', 'LabelManager']

		init: ->
			super()
			@api_endpoint = '/news_labels'
			@ng_route     = 'portal.news_labels'
			@typename     = 'labels_news'

	Admin_Labels_News_Ctrl_List.EXPORT_CTRL()