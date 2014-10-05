define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_UserFields_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_UserFields_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@DEPS    = []

		init: ->
			@fieldDataService = @DataService.get('UserFields')
			@service = @DataService.get 'CustomFields'
			@custom_fields = []
			@sortedListOptions = {
				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')
					displayOrders = []

					$list.find('li').each(->
						displayOrders.push(parseInt($(this).data('id')))
					)

					@fieldDataService.saveDisplayOrder(displayOrders)
					@pingElement('display_orders')
			}

			@entityFieldListOptions =
				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest 'ul'
					displayOrders = []

					$list.find('li').each -> displayOrders.push parseInt $(this).data('id')

					@entityFieldDataService.saveDisplayOrder displayOrders
					@pingElement 'display_orders'

			return

		initialLoad: ->
			promise = @fieldDataService.loadList()
			promise.then( (list) =>
				@custom_fields = list
			)
			@service.all().then (list) => @specific_user_custom_fields = list

			return promise

	Admin_UserFields_Ctrl_List.EXPORT_CTRL()