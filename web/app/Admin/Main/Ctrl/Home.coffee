define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_Home extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_Home'
		@CTRL_AS   = 'Home'

		init: ->
			@online_agents = []
			@offline_agents = []
			return

		initialLoad: ->
			promise = @Api.sendDataGet({
				agents: '/agents'
			}).then( (result) =>
				data = result.data
				@online_agents = []
				@offline_agents = []

				for agent in data.agents.agents
					if agent.is_online_now or agent.id == DP_PERSON_ID
						@online_agents.push(agent)
					else
						@offline_agents.push(agent)
			)

			return promise

	Admin_Main_Ctrl_Home.EXPORT_CTRL()