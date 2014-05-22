define [
	'Admin/Main/Ctrl/Base',
	'Admin/Main/Collection/OrderedDictionary',
], (
	Admin_Ctrl_Base,
	OrderedDictionary
) ->
	class Admin_TicketTriggers_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketTriggers_Ctrl_List'
		@CTRL_AS = 'List'
		@DEPS = ['$state', '$stateParams', '$q', 'TicketAccountsData']

		init: ->
			@dep_triggers   = []
			@email_triggers = []
			@all_triggers   = []
			@triggers       = []

			@depTriggersEnabled = true
			@emailTriggersEnabled = true

			@eventType = @$stateParams.type

			if @$stateParams.type == 'newticket'
				@dpTriggers = @DataService.get('TriggersNew')
			else if @$stateParams.type == 'newreply'
				@dpTriggers = @DataService.get('TriggersReply')
			else
				@dpTriggers = @DataService.get('TriggersUpdate')

			@depData = @DataService.get('TicketDeps')

			@sortedListOptions = {
				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')
					runOrders = []

					$list.find('li').each(->
						runOrders.push(parseInt($(this).data('id')))
					)

					@dpTriggers.saveRunOrder(runOrders)
					@pingElement('run_orders')
			}

			@$scope.$watch('TicketTriggersList.all_triggers', =>
				@sortTriggers()
			, true)

		###
		# Loads the triggers list
		###
		initialLoad: ->
			promises = []

			promises.push @dpTriggers.loadList().then( (list) =>
				window.all_triggers = list
				@all_triggers = list
				@sortTriggers()
			)

			promises.push @depData.loadList().then( (list) =>
				@depList = list
			)

			promises.push @TicketAccountsData.loadList().then( (recs) =>
				@accounts = recs.values()
			)

			return @$q.all(promises)


		###
    	# Sorts triggers into display groups
    	###
		sortTriggers: ->
			@dep_triggers   = []
			@email_triggers = []
			@triggers       = []

			for tr in @all_triggers
				if tr.department
					@dep_triggers.push(tr)
				else if tr.email_account
					@email_triggers.push(tr)
				else
					@triggers.push(tr)


		###
		# Update the enabled state of a trigger
		###
		updateTriggerEnabledState: (trigger) ->
			return @dpTriggers.saveEnabledStateById(trigger.id, trigger.is_enabled)

		updateDepTriggersEnabledState: ->
			return

		updateEmailTriggersEnabledState: ->
			return

		###
		# Show the delete dlg
		###
		startTriggerDelete: (trigger_id) ->
			trigger = null
			for v in @triggers
				if v.id == trigger_id
					trigger = v
					break

			inst = @$modal.open({
				templateUrl: @getTemplatePath('TicketTriggers/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close();

					$scope.dismiss = ->
						$modalInstance.dismiss();
				]
			});

			inst.result.then( =>
				@dpTriggers.deleteTriggerById(trigger.id).then(=>
					@sortTriggers()
					@$state.go('tickets.triggers', {type: @$stateParams.type})
				)
			)

	Admin_TicketTriggers_Ctrl_List.EXPORT_CTRL()