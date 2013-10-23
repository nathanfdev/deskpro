define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_FeedbackStatuses_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_List'
		@CTRL_AS = 'FeedbackStatusesList'
		@DEPS    = ['$rootScope', '$scope', 'FeedbackStatusesData', 'em', 'Api', '$state', 'Growl']
		@CTRL_TYPE = 'list'

		init: ->
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

			statuses_list_promise = @FeedbackStatusesData.loadStatusesList().then((statuses) =>
				@feedback_active_statuses = statuses.active_statuses
				@feedback_closed_statuses = statuses.closed_statuses
			)

			return @$q.all([statuses_list_promise])

	Admin_FeedbackStatuses_Ctrl_List.EXPORT_CTRL()