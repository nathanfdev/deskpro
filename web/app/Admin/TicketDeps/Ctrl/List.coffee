define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketDeps_Ctrl_List'
		@CTRL_AS = 'TicketDepsList'
		@DEPS    = ['$rootScope', '$scope', 'DepartmentData', 'em', 'Api', '$state']

		init: ->
			@addManagedListener(@DepartmentData.deps, 'changed', =>
				@initDepList(@DepartmentData.deps.values())
				@ngApply()
			)

			@DepartmentData.loadDepList().then( (departments) =>
				@initDepList(departments.values())
			)

			@sortedListOptions = {
				axis: 'y',
				update: (ev, data) =>
					$list = data.item.closest('ul')

					postData = {display_orders: []}

					$list.find('li').each(->
						postData.display_orders.push($(this).data('id'))
					)

					promise = @Api.sendPostJson('/ticket_deps/display_order', postData)
			}

		initDepList: (departments) ->
			@departments = departments
			@parent_deps = []
			@child_deps = {}

			for dep in departments
				if dep.parent_id
					if not @child_deps[dep.parent_id]
						@child_deps[dep.parent_id] = []

					@child_deps[dep.parent_id].push(dep)

				else
					@parent_deps.push(dep)

		###*
		* Get the move dep list for use in the delete/move dlg
    	* @return {Array}
		###
		getMoveDepList: (for_dep) ->
			dep_move_list = []
			for dep in @departments
				 if for_dep.id != dep.id
					 if not dep._child_ids
					 	dep_move_list.push(dep)

			return dep_move_list

		###*
		# Show the delete dlg
		###
		startDelete: (for_dep) ->

			if for_dep._child_ids
				@showAlert("You cannot delete a department with sub-departments. Move or delete the sub-departments first.")
				return

			inst = @$modal.open({
				templateUrl: @getTemplatePath('TicketDeps/delete-modal.html'),
				controller: ['$scope', '$modalInstance', 'move_deps_list', ($scope, $modalInstance, move_deps_list) ->
					$scope.selected = {
						move_to_id: "0"
					}
					$scope.move_deps_list = move_deps_list

					$scope.confirm = ->
						$modalInstance.close($scope.selected.move_to_id);

					$scope.dismiss = ->
						$modalInstance.dismiss();
				],
				resolve: {
					move_deps_list: =>
						return @getMoveDepList(for_dep)
				}
			});

			inst.result.then( (move_to) =>
				@deleteDepartment(for_dep, move_to)
			)

		###*
		# Actually do th edelete
		###
		deleteDepartment: (for_dep, move_to) ->
			@Api.sendDelete('/ticket_deps/' + for_dep.id, {
				move_to: move_to
			}).success( =>
				@DepartmentData.deps.remove(for_dep.id)
				@em.removeById('department', for_dep.id)
				@ngApply()

				# if currently viewing the deleted department, then should need to switch state
				if @$state.current.name == 'tickets.ticket_deps.edit' and parseInt(@$state.params.id) == for_dep.id
					@$state.go('tickets.ticket_deps')
			)

	Admin_TicketDeps_Ctrl_List.EXPORT_CTRL()