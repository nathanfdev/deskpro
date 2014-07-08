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
			@map = {} # groups map
			@service = @DataService.get('Tasks')
			@$scope.settings = null
			@$scope.updateAgents = @updateAgents

			@$scope.$watch('settings', (newVal, oldVal) =>
				@$scope.updateAgents() if parseInt(newVal?.enabled)
			)



		initialLoad: ->
			@service.load().then (settings) =>
				@$scope.settings = settings
				console.log settings
				settings.groups.map (group) => @map[group.id] = group



		# update agents checkboxes states
		updateAgents: =>
			for agent in @$scope.settings.agents
				for group in agent.usergroups

					if @map[group.id]?.perms.tasks.use
						agent._checked = true
						agent._disabled = true
						return

				agent._checked = agent.perms.tasks.use
				agent._disabled = false



		save: ->
			@startSpinner('saving')
			@data.save().then(
				=>
					@stopSpinner('saving')
				=>
					@stopSpinner('saving')
			)



	Admin_Tasks_Ctrl_Edit.EXPORT_CTRL()