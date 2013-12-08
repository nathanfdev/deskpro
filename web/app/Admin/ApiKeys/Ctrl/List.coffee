define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ApiKeys_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_ApiKeys_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@keyData = @DataService.get('ApiKeys')

		###
		# Loads the list
		###

		initialLoad: ->

			promise = @keyData.loadList().then( (list) =>
				@list = list
			)

			return promise

	Admin_ApiKeys_Ctrl_List.EXPORT_CTRL()