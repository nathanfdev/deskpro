define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserGroups_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserGroups_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@DEPS = []

		init: ->
			@ugData = @DataService.get('UserGroups')
			@system_groups_enabled = {}

		###
		# Loads the list
		###

		initialLoad: ->

			promise = @ugData.loadList().then( (list) =>
				@list = list
			)

			return promise

		###
 	# @param {Object} user_group - usergroup model which enabled / disabled state we want to toggle
		###

		toggleUserGroup: (user_group) ->

			if user_group.is_enabled
				val = '1'
			else
				val = '0'

			@Api.sendPost('/user_groups/set-enabled/' + user_group.id + '/' + val)

	Admin_UserGroups_Ctrl_List.EXPORT_CTRL()