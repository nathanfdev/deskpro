define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ChatFields_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_ChatFields_Ctrl_List'
		@CTRL_AS = 'ChatFieldsList'
		@DEPS    = []

		init: ->
			@chat_fields = @DataService.get('ChatFields')
			@custom_fields = []
			return

		initialLoad: ->
			promise = @chat_fields.loadList()
			promise.then( (list) =>
				@custom_fields = list
			)

			return promise


	Admin_ChatFields_Ctrl_List.EXPORT_CTRL()