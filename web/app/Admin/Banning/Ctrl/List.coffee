define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Banning_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Banning_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@banData = @DataService.get('Bans')

		###
		# Loads the list
		###

		initialLoad: ->

			promise = @banData.loadList().then( (list) =>
				@list = list
			)

			return promise

	Admin_Banning_Ctrl_List.EXPORT_CTRL()