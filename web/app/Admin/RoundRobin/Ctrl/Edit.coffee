define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_RoundRobin_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_RoundRobin_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS = ['$stateParams', 'Growl']



		init: ->
			@robin = {}
			@agents = []
			@bulk = null
			@service = @DataService.get 'RoundRobin'
			@serviceAgents = @DataService.get 'Agents'
			@serviceDeps = @DataService.get 'TicketDeps'
			@serviceGroups = @DataService.get 'AgentGroups'
			@serviceTeams = @DataService.get 'AgentTeams'

			@groups = []
			@teams = []
			@deps = []



		initialLoad: ->
			promises = [@serviceAgents.all(), @service.get(parseInt(@$stateParams.id || 0)),
			            @serviceDeps.all(), @serviceGroups.all(), @serviceTeams.all(),]

			@$q.all(promises).then (res) =>
				@agents = res[0]
				@mapFormModel res[1]
				@deps = res[2]
				@groups = res[3]
				@teams = res[4]

				console.log @deps



		mapFormModel: (model) ->
			@robin.agents = []
			return if !model?

			@robin.id = model.id
			@robin.title = model.title

			@serviceAgents.get(model.next.id).then((agent) => @robin.next = agent) if model.next?
			# remap agents to list models
			promises = []
			model.agents.map (agent) =>
				promise = @serviceAgents.get(agent.id).then (agent) => @robin.agents.push agent
				promises.push promise

			@$q.all(promises).then => @sortAgents()



		sortAgents: ->
			@agents.sort (a, b) =>
				indexA = @robin.agents.indexOf a
				indexB = @robin.agents.indexOf b
				return 0 if indexA == indexB
				if indexA < indexB then return 1 else return -1



		handleAgent: (agent) ->
			return if @agents.indexOf(agent) == -1
			index = @robin.agents.indexOf agent
			if index == -1 then @robin.agents.push agent else @robin.agents.splice(index, 1)



		handleBulk: ->
			return if !@bulk?
			params = @bulk.split '.'
			add = {}

			switch params[0]
				when 'd'
					@serviceDeps.get(params[1]).then (dep) =>
						for agent in @agents
							for agentData in dep.permissions.users
								if 'full' == agentData.name and agent.id == agentData.id and @robin.agents.indexOf(agent) == -1
									add[agent.id] = agent

						for id, agent of add
							@handleAgent agent
						@sortAgents()



		save: ->
			if @$scope.Form.$invalid then return

			@startSpinner 'saving'
			@service.set(@robin).then(
				(model) =>
					@stopSpinner 'saving'
					@mapFormModel model
					@$state.go 'tickets.roundrobin'
					@Growl.success 'Saved'
				(res) =>
					@stopSpinner 'saving'
					@Growl.error res.info
			)



		delete: ->
			title = @getRegisteredMessage 'modal_title'
			msg = @getRegisteredMessage 'modal_message'
			state = @$state

			_del = (modal) =>
				@service.remove(@robin).then ->
					modal.dismiss()
					state.go 'tickets.roundrobin'

			@$modal.open({
				templateUrl: @getTemplatePath('Index/modal-confirm.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->

					$scope.title = title
					$scope.message = msg

					$scope.dismiss = ->
						$modalInstance.dismiss()

					$scope.confirm = ->
						_del $modalInstance
				]
			})


	Admin_RoundRobin_Ctrl_Edit.EXPORT_CTRL()