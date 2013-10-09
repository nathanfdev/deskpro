define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketDeps_Ctrl_List'
		@CTRL_AS = 'TicketDepsList'
		@DEPS    = ['$rootScope', '$scope', 'DepartmentData', 'em', 'Api', '$state', '$translate', 'Growl']
		@CTRL_TYPE = 'list'

		init: ->
			@departments_count = 0;
			@dep_settings = {}

			@sortedListOptions = {
				axis: 'y',
				handle: '.drag-handle',
				update: (ev, data) =>
					$list = data.item.closest('ul')

					postData = {display_orders: []}

					$list.find('li').each(->
						postData.display_orders.push($(this).data('id'))
					)

					promise = @Api.sendPostJson('/ticket_deps/display_order', postData)
					@pingElement('display_orders')
			}

		initialLoad: ->

			dep_promise = @DepartmentData.loadDepList().then( (departments) =>
				@initDepList(departments.values())

				@addManagedListener(@DepartmentData.deps, 'changed', =>
					@initDepList(@DepartmentData.deps.values())
					@ngApply()
				)
			)

			data_promise = @Api.sendDataGet([
					'/ticket_deps/settings'
			]).then( (res) =>
				settings = res.data.api_ticket_deps_settings

				@dep_settings.default_id    = parseInt(settings['core.default_ticket_dep']) || 0
				@dep_settings.name_singular = settings['core.phrase_department_singular']
				@dep_settings.name_plural   = settings['core.phrase_department_plural']

				if @dep_settings.name_singular or @dep_settings.name_plural
					@dep_settings.do_rename = true
			)

			return @$q.all([dep_promise, data_promise]).then(=>
				if @dep_settings.default_id
					found = false
					for d in @default_dep_list
						if d.id == @dep_settings.default_id
							found = true
							break

					if not found
						@dep_settings.default_id = 0

				if not @dep_settings.default_id or @dep_settings.default_id == 0
					@dep_settings.default_id = @default_dep_list[0].id
			)


		initDepList: (departments) ->
			@departments = departments
			@departments_count = departments.lenght
			@parent_deps = []
			@child_deps = {}
			@default_dep_list = []

			for dep in departments
				if dep.parent_id
					if not @child_deps[dep.parent_id]
						@child_deps[dep.parent_id] = []

					@child_deps[dep.parent_id].push(dep)

					@default_dep_list.push(dep)

				else
					@parent_deps.push(dep)
					if not dep._child_ids
						@default_dep_list.push(dep)

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
					$scope.move_deps_list = move_deps_list
					$scope.selected = {
						move_to_id: move_deps_list[0].id
					}

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
				@DepartmentData.resetHierarchy()
				@em.removeById('department', for_dep.id)
				@ngApply()

				# if currently viewing the deleted department, then should need to switch state
				if @$state.current.name == 'tickets.ticket_deps.edit' and parseInt(@$state.params.id) == for_dep.id
					@$state.go('tickets.ticket_deps')
			)

		saveSettings: ->
			if not @dep_settings.do_rename
				@dep_settings.name_singular = ''
				@dep_settings.name_plural = ''

			postData = {
				settings: {
					'core.default_ticket_dep':         @dep_settings.default_id,
					'core.phrase_department_singular': @dep_settings.name_singular,
					'core.phrase_department_plural':   @dep_settings.name_plural
				}
			}

			@startSpinner('saving_settings')

			@Api.sendPostJson('/ticket_deps/settings', postData).then(=>
				@stopSpinner('saving_settings').then(=>
					@Growl.success(@getRegisteredMessage('saved_settings'))
				)
			)

	Admin_TicketDeps_Ctrl_List.EXPORT_CTRL()