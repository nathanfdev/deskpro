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

					$list.find('li').each(->
						postData.display_orders.push($(this).data('id'))
					)

					# @TODO implement ordering

					promise = @Api.sendPostJson('/feedback_statuses/display_order', postData)
					@pingElement('display_orders')
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

	Admin_FeedbackStatuses_Ctrl_List.EXPORT_CTRL()