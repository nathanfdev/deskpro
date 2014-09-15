define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_Usersources_Ctrl_UsersourcesList extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Usersources_Ctrl_UsersourcesList'
		@CTRL_AS = 'ListCtrl'
		@DEPS = []

		init: ->
			@usersourcesDataService = @DataService.get('Usersources')
			@sortedListOptions = {
				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')
					displayOrders = []
					$list.find('li').each(->
						displayOrders.push(parseInt($(this).data('id')))
					)
					@usersourcesDataService.saveDisplayOrder(displayOrders)
					@pingElement('display_orders')
			}

		initialLoad: ->
			promise = @Api.sendGet('/usersources/user').then( (result) =>
				@usersources = result.data.usersources
			)
			return promise

	Admin_Usersources_Ctrl_UsersourcesList.EXPORT_CTRL()