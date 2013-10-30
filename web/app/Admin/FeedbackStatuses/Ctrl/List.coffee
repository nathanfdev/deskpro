define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_FeedbackStatuses_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_List'
		@CTRL_AS = 'FeedbackStatusesList'
		@DEPS    = ['$rootScope', '$scope', 'FeedbackStatusesData', 'em', 'Api', '$state', 'Growl']
		@CTRL_TYPE = 'list'

		init: ->

			@$scope.activeType = 'active'
			@$scope.closedType = 'closed'

			@feedback_active_statuses = [];
			@feedback_closed_statuses = [];

			@sortedListOptions = {
				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')

					postData = {display_orders: []}

					x = 0
					em = @em

					status_type = 'active'

					$list.find('li').each(->

						x += 10
						feedback_status_id = parseInt($(this).data('id'))

						if feedback_status_id
							feedback_status = em.getById('feedback_status', feedback_status_id)

							if feedback_status
								feedback_status.display_order = x
								status_type = feedback_status.status_type

						postData.display_orders.push(feedback_status_id)
					)

					promise = @Api.sendPostJson('/feedback_statuses/display_order', postData)
					@pingElement('display_orders_' + status_type)
			}

		initialLoad: ->

			list_promise = @FeedbackStatusesData.loadList().then( (recs) =>

				@feedback_active_statuses = recs.active_statuses.values()
				@feedback_closed_statuses = recs.closed_statuses.values()

				@addManagedListener(@FeedbackStatusesData.recs.active_statuses, 'changed', =>

					@feedback_active_statuses = @FeedbackStatusesData.recs.active_statuses.values()
					@ngApply()
				)

				@addManagedListener(@FeedbackStatusesData.recs.closed_statuses, 'changed', =>

					@feedback_closed_statuses = @FeedbackStatusesData.recs.closed_statuses.values()
					@ngApply()
				)
			)

			return @$q.all([list_promise])

		###
  # Show the delete dlg
  ###

		startDelete: (feedback_status) ->

			if @['feedback_' + feedback_status.status_type + '_statuses'].length <= 1
				@showAlert('@no_delete_last');
				return

			inst = @$modal.open({
				templateUrl: @getTemplatePath('FeedbackStatuses/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close();

					$scope.dismiss = ->
						$modalInstance.dismiss();
				]
			});

			inst.result.then(=>
				@deleteFeedbackStatus(feedback_status)
			)

		###
		# Actually do the delete
		###

		deleteFeedbackStatus: (feedback_status) ->

			@Api.sendDelete('/feedback_statuses/' + feedback_status.id).success(=>

				@FeedbackStatusesData.remove(feedback_status.id)
				@ngApply()

				# if currently viewing the deleted feedback status, then should need to switch state
				if @$state.current.name == 'portal.feedback_statuses.edit' and parseInt(@$state.params.id) == feedback_status.id
					@$state.go('portal.feedback_statuses')
			)

	Admin_FeedbackStatuses_Ctrl_List.EXPORT_CTRL()