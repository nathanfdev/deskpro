define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_ChatDeps_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_ChatDeps_Ctrl_List'
		@CTRL_AS = 'ListCtrl'

		init: ->
			@depData = @DataService.get('ChatDeps')

			@sortedListOptions = {
				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')

					order = []
					$list.find('li').each(->
						order.push(parseInt($(this).data('id')))
					)

					@depData.saveDisplayOrders(order)
					@pingElement('display_orders')
			}

		###
		# Loads the dep list
		###

		initialLoad: ->

			promise = @depData.loadList().then( (list) =>
				@depList = list
				@deps = @depData.listModels
			)

			return promise

	Admin_ChatDeps_Ctrl_List.EXPORT_CTRL()