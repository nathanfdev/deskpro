define [
	'Admin/Main/Ctrl/Base',
	'Admin/Main/Collection/OrderedDictionary',
], (
	Admin_Ctrl_Base,
	OrderedDictionary
) ->
	class Admin_TicketTriggers_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketTriggers_Ctrl_List'
		@CTRL_AS = 'TicketTriggersList'
		@DEPS = ['$state', '$stateParams', 'TriggersNew', 'TriggersReply', 'TriggersUpdate']
		@CTRL_TYPE = 'list'

		init: ->
			@triggers = null
			@eventType = @$stateParams.type

			if @$stateParams.type == 'newticket'
				@dpTriggers = @TriggersNew
			else if @$stateParams.type == 'newreply'
				@dpTriggers = @TriggersReply
			else
				@dpTriggers = @TriggersUpdate

			@sortedListOptions = {
				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')

					postData = {run_orders: []}

					$list.find('li').each(->
						postData.run_orders.push($(this).data('id'))
					)

					promise = @Api.sendPostJson('/ticket_triggers/run_order', postData)
					@pingElement('run_orders')
			}

		###
		# Loads the triggers list
		###
		initialLoad: ->
			promise = @dpTriggers.loadList().then( (recs) =>
				@triggers = recs.values()

				@addManagedListener(recs, 'changed', =>
					@triggers = recs.values()
					@ngApply()
				)
			)

			return promise


		###
		# Update the enabled state of a trigger
		###
		updateTriggerEnabledState: (trigger) ->
			if trigger.is_enabled
				@Api.sendPost("/ticket_triggers/#{trigger.id}/enable")
			else
				@Api.sendPost("/ticket_triggers/#{trigger.id}/disable")


		###
		# Show the delete dlg
		###
		startTriggerDelete: (trigger) ->
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
				@deleteTrigger(trigger)
			)

		###
		# Actually do the delete
		###
		deleteTrigger: (trigger) ->
			@dpTriggers.remove(trigger.id)
			@Api.sendDelete('/ticket_triggers/' + trigger.id).success( =>
				# if currently viewing the deleted department, then should need to switch state
				if @$state.current.name == 'tickets.ticket_triggers.edit' and parseInt(@$state.params.id) == trigger.id
					@$state.go('tickets.ticket_triggers')
			)

	Admin_TicketTriggers_Ctrl_List.EXPORT_CTRL()