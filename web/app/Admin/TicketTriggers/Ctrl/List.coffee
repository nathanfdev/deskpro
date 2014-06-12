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
		@DEPS = ['$state', '$stateParams', '$q', 'TicketAccountsData', '$timeout']

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

					eventType = @eventType
					$list.find('li').each(->
						id = $(this).data('trigger-id')
						if id
							if id == 'departments'
								if eventType == 'update' then id = 'departments_changed'
								runOrders.push(id)
							else if id == 'emailaccounts'
								runOrders.push(id)
							else
								runOrders.push(parseInt(id))
					)

					@dpTriggers.saveRunOrder(runOrders)
					@pingElement('run_orders')
			}

			@$scope.$watch('List.all_triggers', =>
				@sortTriggers()
			, true)

		###
		# Loads the triggers list
		###
		initialLoad: ->
			promises = []

			promises.push @dpTriggers.loadList(true).then( (list) =>
				window.all_triggers = list
				@all_triggers = list

				@depTriggersEnabled   = @dpTriggers.department_triggers_enabled
				@emailTriggersEnabled = @dpTriggers.emailaccount_triggers_enabled

				@sortTriggers()

				@$scope.dep_order = 0
				@$scope.emailaccount_order = 0

				for t in @all_triggers
					if not @$scope.dep_order and t.department
						@$scope.dep_order = t.run_order
					if not @$scope.emailaccount_order and t.email_account
						@$scope.emailaccount_order = t.run_order
			)

			if @eventType == 'newticket' or @eventType == 'update'
				promises.push @depData.loadList(true).then( (list) =>
					@depList = list
				)

			if @eventType == 'newticket'
				promises.push @TicketAccountsData.loadList(true).then( (recs) =>
					@accounts = recs.values()
					@accounts = @accounts.filter((x) -> x.account_type != 'outgoing')
				)

			d = @$q.defer()


			# run re-order stuff (from dpMoveListToPos)
			# while loading indicator is still spinning,
			# eliminates the visual stutter
			@$q.all(promises).then(=>
				@$timeout(=>
					@$scope.$broadcast('resetDisplayOrders')
					@$timeout(->
						d.resolve()
					, 1)
				, 1)
			)

			return d.promise


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
			@dpTriggers.saveGroupEnabledState('departments', @depTriggersEnabled)
			return

		updateEmailTriggersEnabledState: ->
			@dpTriggers.saveGroupEnabledState('email_accounts', @emailTriggersEnabled)
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