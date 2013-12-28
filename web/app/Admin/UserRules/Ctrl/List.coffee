define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserRules_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserRules_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@userRulesData = @DataService.get('UserRules')

		###
		# Loads the list
		###

		initialLoad: ->

			promise = @userRulesData.loadList().then( (list) =>
				@list = list
			)

			return promise

	Admin_UserRules_Ctrl_List.EXPORT_CTRL()