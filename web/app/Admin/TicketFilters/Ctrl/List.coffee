define [
	'Admin/Main/Ctrl/Base'
], (
	Admin_Ctrl_Base
) ->
	class Admin_TicketFilters_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketFilters_Ctrl_List'
		@CTRL_AS = 'ListCtrl'
		@DEPS = ['$state', '$stateParams', 'DataService']
		@CTRL_TYPE = 'list'

		init: ->
			@list = []
			@filterData = @DataService.get('TicketFilters')

			@sortedListOptions = {
				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')

					orders = []
					$list.find('li').each(->
						id = parseInt($(this).data('id'))
						console.log(id)

						if id
							orders.push(id)
					)

					@filterData.saveDisplayOrder(orders).then( =>
						@pingElement('display_orders')
					)
			}

		initialLoad: ->
			promise = @filterData.loadList()
			promise.then( (list) =>

				@list = list

				if @$state.current.name == 'tickets.ticket_filters'
					if @list[0]
						@$state.go('tickets.ticket_filters.edit', { id: @list[0].id })
					else
						@$state.go('tickets.ticket_filters.create')
			)

			return promise


		###
		# Show the delete dlg
		###
		startDelete: (filter) ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('TicketFilters/delete-modal.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.confirm = ->
						$modalInstance.close();

					$scope.dismiss = ->
						$modalInstance.dismiss();
				]
			});

			inst.result.then( =>
				@filterData.deleteFilterId(filter.id).then(=>
					if @$state.current.name == 'tickets.ticket_filters.edit' and parseInt(@$state.params.id) == filter.id
						@$state.go('tickets.ticket_filters')
				)
			)

	Admin_TicketFilters_Ctrl_List.EXPORT_CTRL()