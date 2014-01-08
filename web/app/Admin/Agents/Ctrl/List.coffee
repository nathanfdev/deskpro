define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Agents_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Agents_Ctrl_List'
		@CTRL_AS   = 'ListCtrl'
		@DEPS      = []

		init: ->
			return

		initialLoad: ->
			promise = @Api.sendGet('/agents').then( (result) =>
				@agents = result.data.agents
			)
			return promise

		removeAgentFromList: (id) ->
			@agents = @agents.filter((x) -> x.id != id)

		updateAgent: (agent) ->
			for a in @agents
				if a.id == agent.id
					a.display_name = agent.display_name

		addAgent: (id, name) ->
			@agents.push({
				id: id,
				display_name: name
			})

	Admin_Agents_Ctrl_List.EXPORT_CTRL()