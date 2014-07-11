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



		initialLoad: ->
			promises = [@serviceAgents.all(), @service.get(parseInt(@$stateParams.id || 0))]

			@$q.all(promises).then (res) =>
				@agents = res[0]
				@mapFormModel res[1]



		mapFormModel: (model) ->
			@robin.agents = []
			return if !model?

			@robin.id = model.id
			@robin.title = model.title

			@serviceAgents.get(model.next.id).then((agent) => @robin.next = agent) if model.next?
			# remap agents to list models
			model.agents.map (agent) =>
				@serviceAgents.get(agent.id).then (agent) => @robin.agents.push agent



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