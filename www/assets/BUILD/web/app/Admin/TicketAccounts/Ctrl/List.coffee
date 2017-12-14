define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketAccounts_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketAccounts_Ctrl_List'
    @CTRL_AS = 'TicketAccountsList'
    @DEPS    = ['TicketAccountsData']

    init: ->
      @accounts = []

    initialLoad: ->
      list_promise = @TicketAccountsData.loadList().then( (recs) =>
        @accounts = recs.values()

        if @$state.current.name == 'emails.ticket_accounts'
          if @accounts[0]
            @$state.go('emails.ticket_accounts.edit', { id: @accounts[0].id })
          else
            @$state.go('emails.ticket_accounts.create')

        @addManagedListener(@TicketAccountsData.recs, 'changed', =>
          @TicketAccountsData.recs.reorder()
          @accounts = @TicketAccountsData.recs.values()
          @ngApply()
        )
      )

      return @$q.all([list_promise]);

    ###
    # Show the delete dlg
    ###
    startDelete: (for_acc_id) ->

      for_acc = null
      for v in @accounts
        if v.id == for_acc_id
          for_acc = v

      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketAccounts/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then(=>
        @deleteAccount(for_acc)
      )

    ###for_acc
    # Actually do th edelete
    ###
    deleteAccount: (acc) ->
      @Api.sendDelete('/email_accounts/' + acc.id).success( =>
        @TicketAccountsData.remove(acc.id)
        @ngApply()

        # if currently viewing the deleted account, then should need to switch state
        if @$state.current.name == 'emails.ticket_accounts.edit' and parseInt(@$state.params.id) == acc.id
          @$state.go('emails.ticket_accounts')
      )

  Admin_TicketAccounts_Ctrl_List.EXPORT_CTRL()