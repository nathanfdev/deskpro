define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketStatuses_Ctrl_EditAwaitingUser extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditAwaitingUser'
    @CTRL_AS = 'TicketStatusEdit'
    @DEPS = []

    init: ->
      @$scope.getCount = => @$scope.$parent.TicketStatusesList?.getStatusCount('awaiting_user')

      @$scope.editTemplate = (esc) =>
        @$modal.open({
          templateUrl: DP_BASE_ADMIN_URL+'/load-view/Templates/modal-email-editor.html',
          controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
          resolve:
            templateName: -> 'DeskPRO:emails_user:ticket-rate.html.twig'
        })



    save: ->
      @Growl.success @getRegisteredMessage('saved_settings')
      @$scope.$broadcast 'trigger.save'
      @$timeout(
        => @$state.go @$state.current, {}, {reload: true}
        200
      )




  Admin_TicketStatuses_Ctrl_EditAwaitingUser.EXPORT_CTRL()