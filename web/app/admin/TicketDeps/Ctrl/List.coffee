define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketDeps_Ctrl_List'
		@CTRL_AS = 'TicketDepsList'
		@DEPS    = ['$rootScope', '$scope', 'DepartmentData']

		init: ->
			@addManagedListener(@DepartmentData.deps, 'changed', =>
				@departments = @DepartmentData.deps.values()
				@ngApply()
			)

			@DepartmentData.loadDepList().then( (departments) =>
				@departments = departments.values()
			)

	Admin_TicketDeps_Ctrl_List.EXPORT_CTRL()