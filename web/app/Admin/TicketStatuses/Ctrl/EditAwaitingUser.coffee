define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketStatuses_Ctrl_EditAwaitingUser extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditAwaitingUser'
    @CTRL_AS = 'TicketStatusEdit'
    @DEPS = []

    init: ->
      @$scope.getCount = => @$scope.$parent.TicketStatusesList?.getStatusCount('awaiting_user')
      return

  Admin_TicketStatuses_Ctrl_EditAwaitingUser.EXPORT_CTRL()