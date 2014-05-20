define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_AgentGroups_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_AgentGroups_Ctrl_List'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

		init: ->
			return

		initialLoad: ->
			promise = @Api.sendGet('/agent_groups').then( (result) =>
				@groups = result.data.groups
			)
			return promise

		addGroup: (group) ->
			@groups.push(group)

		removeGroupById: (groupId) ->
			groupId = parseInt(groupId)
			@groups = @groups.filter((x) -> x.id != groupId)

		renameGroupById: (groupId, title) ->
			@groups.filter((x) -> x.id == groupId).map((x) -> x.title = title)

	Admin_AgentGroups_Ctrl_List.EXPORT_CTRL()