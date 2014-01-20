define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserGroups_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserGroups_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@DEPS = []

		init: ->
			@ugData = @DataService.get('UserGroups')

		initialLoad: ->
			promise = @ugData.loadList().then( (list) =>
				@list = list
			)

			return promise

	Admin_UserGroups_Ctrl_List.EXPORT_CTRL()