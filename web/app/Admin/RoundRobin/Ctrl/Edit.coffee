define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Arrays'], (Admin_Ctrl_Base, Arrays) ->
	class Admin_RoundRobin_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_RoundRobin_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS = ['$stateParams', 'Growl', '$timeout']



		init: ->
			@robin = {}
			@agents = []
			@nextAgentInQueue = null
			@bulk = null
			@service = @DataService.get 'RoundRobin'
			@serviceAgents = @DataService.get 'Agents'
			@serviceDeps = @DataService.get 'TicketDeps'
			@serviceGroups = @DataService.get 'AgentGroups'
			@serviceTeams = @DataService.get 'AgentTeams'

			@groups = []
			@teams = []
			@deps = []

			@sortedListOptions = {
				axis: 'y',
				items: 'li.sortable'
				handle: '.drag-handle'
			}



		initialLoad: ->
			@service.loadList true
			promises = [@serviceAgents.all(), @service.get(parseInt(@$stateParams.id || 0)),
			            @serviceDeps.all(), @serviceGroups.all(), @serviceTeams.all(),]

			@$q.all(promises).then (res) =>
				@agents = res[0]
				@mapFormModel res[1]
				@deps = res[2]
				@groups = res[3]
				@teams = res[4]



		mapFormModel: (model) ->
			@robin.agents = []
			return if !model?

			@robin.id = model.id
			@robin.title = model.title

			@serviceAgents.get(model.next.id).then((agent) => @robin.next = agent) if model.next?
			# remap agents to list models
			promises = []
			model.agents.map (data) =>
				promise = @serviceAgents.get(data.id).then (agent) => @robin.agents.push agent
				promises.push promise

			@$q.all(promises).then => @sortAgents()



		sortAgents: ->
			@agents.sort (a, b) =>
				indexA = @robin.agents.indexOf a
				indexB = @robin.agents.indexOf b
				return 0 if indexA == indexB
				return 1 if indexA == -1
				return -1 if indexB == -1
				if indexA < indexB then return -1 else return 1



		handleAgent: (agent) ->
			return if @agents.indexOf(agent) == -1
			index = @robin.agents.indexOf agent
			if index == -1 then @robin.agents.unshift agent else @robin.agents.splice(index, 1)



		handleBulk: ->
			return if !@bulk?
			params = @bulk.split '.'

			findAgent = (id) =>
				return Arrays.find(@agents, (a) -> a.id == id)

			switch params[0]
				when 'd'
					@Api.sendGet("/ticket_deps/#{params[1]}?with_agents_list=1").success( (data) =>
						return if not data || not data.agents_list

						for a in data.agents_list
							agent = findAgent(a.id)
							if agent then @handleAgent(agent)
							@sortAgents()
					)

				when 'g'
					@Api.sendGet("/agent_groups/#{params[1]}").success( (data) =>
						return if not data || not data.group.members

						for a in data.team.members
							agent = findAgent(a.id)
							if agent then @handleAgent(agent)
							@sortAgents()
					)

				when 't'
					@Api.sendGet("/agent_teams/#{params[1]}").success( (data) =>
						return if not data || not data.team.members

						for a in data.team.members
							agent = findAgent(a.id)
							if agent then @handleAgent(agent)
							@sortAgents()
					)


		save: ->
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

			@service.checkTriggers(@robin.id).then (data) =>
				@active_triggers = data.active_triggers

				@$timeout(
					=>
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
					1
				)



	Admin_RoundRobin_Ctrl_Edit.EXPORT_CTRL()