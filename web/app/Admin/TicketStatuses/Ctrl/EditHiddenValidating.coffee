define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketStatuses_Ctrl_EditHiddenValidating extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenValidating'
    @CTRL_AS = 'TicketStatusEdit'
    @DEPS = []

    init: ->
      @$scope.getCount = => @$scope.$parent.TicketStatusesList?.getStatusCount('hidden_validating')
      return

  Admin_TicketStatuses_Ctrl_EditHiddenValidating.EXPORT_CTRL()