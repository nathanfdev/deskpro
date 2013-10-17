define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketFeedbackStatuses_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFeedbackStatuses_Ctrl_List'
		@CTRL_AS = 'TicketFeedbackStatusesList'
		@DEPS = ['$scope', 'TicketFeedbackStatusesData']
		@CTRL_TYPE = 'list'

		init: ->
			@statuses = []
			@active_statuses_length = ''
			@closed_statuses_length = ''
			@add_mode = false
			@$scope.escape_url = (text)->
				return encodeURIComponent(text)

			@$scope.$watchCollection('statuses',
			(newValue)->
				if !angular.isArray(newValue) then return
				@active_statuses_length = _.where(newValue, {is_active: true}).length
				@closed_statuses_length = _.where(newValue, {is_active: false}).length
				return
			, true)

			@sortedListOptions = {
				axis:   'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')

					postData = {display_orders: []}

					$list.find('li').each(->
						postData.display_orders.push($(this).data('id'))
					)

					promise = @Api.sendPostJson('/feedback/statuses/order', postData)
					@pingElement('display_orders')
			}
			return

		initialLoad: ->
			list_promise = @TicketFeedbackStatusesData.loadList().then((recs) =>
				@statuses = recs.values()
				
				@addManagedListener(@TicketFeedbackStatusesData.recs, 'changed', =>
					@statuses = @TicketFeedbackStatusesData.recs.values()
					@ngApply()
				)				
			)

			return @$q.all([list_promise]);

		startDelete: (status) ->
			status.delete_mode = true
			inst = @$modal.open({
				templateUrl: @getTemplatePath('TicketFeedbackStatuses/delete-modal.html'),
				controller:  ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close();

					$scope.dismiss = ->
						$modalInstance.dismiss();
						status.delete_mode = false
				]
			});

			inst.result.then(=>
				@deleteStatus(status)
			)

			inst.result.catch(=>
				status.delete_mode = false
			)

		deleteStatus: (status) ->
			@Api.sendDelete('/feedback/statuses/'+status.id)
			.success(=>
					@TicketFeedbackStatusesData.remove(status)

					# if currently viewing the deleted account, then should need to switch state
					if @$state.current.name=='tickets.feedback.statuses.edit' and @$state.params.id==status.id
						@$state.go('tickets.feedback.statuses')
				)
			.finally(=>
					status.delete_mode = false
				)

		updateEnabledState: (status) ->
			@Api.sendPost('/feedback/statuses/switch/'+status.id)
			.success(=>
					status.is_enabled = !status.is_enabled
					@pingElement('statuses_enabled')
				)

	Admin_TicketFeedbackStatuses_Ctrl_List.EXPORT_CTRL()