define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_RoundRobin_Ctrl_Edit extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_RoundRobin_Ctrl_Edit'
		@CTRL_AS = 'EditCtrl'
		@DEPS = ['$stateParams', 'Growl']



		init: ->
			@robin = {}
			@agents = []
			@service = @DataService.get 'RoundRobin'
			@serviceAgents = @DataService.get 'Agents'
			@serviceDeps = @DataService.get 'TicketDeps'
			@serviceGroups = @DataService.get 'AgentGroups'
			@serviceTeams = @DataService.get 'AgentTeams'

			@groups = []
			@teams = []
			@deps = []

			@$scope.batch = null



		initialLoad: ->
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
			model.agents.map (agent) =>
				promise = @serviceAgents.get(agent.id).then (agent) => @robin.agents.push agent
				promises.push promise

			@$q.all(promises).then => @sortAgents()



		sortAgents: ->
			console.log @agents
			@agents.sort (a, b) =>
				indexA = @robin.agents.indexOf a
				indexB = @robin.agents.indexOf b
				return 0 if indexA == indexB
				if indexA < indexB then return 1 else return -1



		handleAgent: (agent) ->
			index = @robin.agents.indexOf agent
			if index == -1 then @robin.agents.push agent else @robin.agents.splice(index, 1)
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