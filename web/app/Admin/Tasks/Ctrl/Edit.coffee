define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_Tasks_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Tasks_Ctrl_Edit'
		@CTRL_AS   = 'EditCtrl'
		@DEPS      = ['$stateParams']

		init: ->
			@data = null
			@map = {} # groups map
			@service = @DataService.get('Tasks')
			@$scope.settings = null
			@$scope.agents = []
			@$scope.groups = []
			@$scope.updateAgents = @updateAgents


			@$scope.$watch('settings', (newVal, oldVal) =>
				if parseInt(newVal?.enabled)
					 if @data then @$scope.updateAgents() else @loadPerms()
			)



		initialLoad: ->
			@service.load().then (settings) => @$scope.settings = settings



		# load perms lists
		loadPerms: ->
			@Api.sendDataGet({
				agents: '/agents?with_perms=1'
				groups: '/agent_groups?with_perms=1'
			}).then (data) =>
				@data = data.data if data?.data?
				@$scope.agents = @data.agents.agents if @data.agents?.agents?

				if @data.groups?.groups?
					@$scope.groups = @data.groups.groups
					@data.groups.groups.map (group) => @map[group.id] = group

				@updateAgents()



		# update agents checkboxes states
		updateAgents: =>
			for agent in @$scope.agents
				for group in agent.usergroups

					if @map[group.id].perms.tasks.use
						agent._checked = true
						agent._disabled = true
						return

				agent._checked = agent.perms.tasks.use
				agent._disabled = false



		save: ->
			@startSpinner('saving')
			@data.save().then(
				=>
					console.log 'success'
					@stopSpinner('saving')
				=>
					console.log 'fail'
					@stopSpinner('saving')
			)



	Admin_Tasks_Ctrl_Edit.EXPORT_CTRL()