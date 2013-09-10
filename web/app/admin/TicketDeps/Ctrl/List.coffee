define ['Admin/Main/Ctrl/Base', 'Admin/App'], (Admin_Ctrl_Base) ->
	class Admin_TicketDeps_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketDeps_Ctrl_List'
		@CTRL_AS = 'TicketDepsList'
		@DEPS    = ['$scope', 'DepartmentData']

		init: ->
			console.log(@DepartmentData)
			@departments = null

			promise = @DepartmentData.loadDepList()
			promise.then( (departments) =>
				console.log(departments)
				@departments = departments.values()
				console.log(@departments)
			)

	Admin_TicketDeps_Ctrl_List.EXPORT_CTRL()