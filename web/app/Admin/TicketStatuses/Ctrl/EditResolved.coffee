define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_TicketStatuses_Ctrl_EditResolved extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditResolved'
		@CTRL_AS = 'TicketStatusEdit'
		@DEPS = []

		init: ->
			@$scope.getCount = => @$scope.$parent.TicketStatusesList?.getStatusCount('resolved')
			return

	Admin_TicketStatuses_Ctrl_EditResolved.EXPORT_CTRL()