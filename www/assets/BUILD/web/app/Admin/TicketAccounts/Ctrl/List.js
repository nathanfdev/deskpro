// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketAccounts_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketAccounts_Ctrl_List';
      this.CTRL_AS = 'TicketAccountsList';
      this.DEPS    = ['TicketAccountsData'];
    }

    init() {
      return this.accounts = [];
    }

    initialLoad() {
      const list_promise = this.TicketAccountsData.loadList().then( recs => {
        this.accounts = recs.values();

        if (this.$state.current.name === 'emails.ticket_accounts') {
          if (this.accounts[0]) {
            this.$state.go('emails.ticket_accounts.edit', { id: this.accounts[0].id });
          } else {
            this.$state.go('emails.ticket_accounts.create');
          }
        }

        return this.addManagedListener(this.TicketAccountsData.recs, 'changed', () => {
          this.TicketAccountsData.recs.reorder();
          this.accounts = this.TicketAccountsData.recs.values();
          return this.ngApply();
        });
      });

      return this.$q.all([list_promise]);
    }

    /*
     * Show the delete dlg
     */
    startDelete(for_acc_id) {

      let for_acc = null;
      for (let v of Array.from(this.accounts)) {
        if (v.id === for_acc_id) {
          for_acc = v;
        }
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketAccounts/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => {
        return this.deleteAccount(for_acc);
      });
    }

    /*for_acc
     * Actually do th edelete
     */
    deleteAccount(acc) {
      return this.Api.sendDelete(`/email_accounts/${acc.id}`).success( () => {
        this.TicketAccountsData.remove(acc.id);
        this.ngApply();

        // if currently viewing the deleted account, then should need to switch state
        if ((this.$state.current.name === 'emails.ticket_accounts.edit') && (parseInt(this.$state.params.id) === acc.id)) {
          return this.$state.go('emails.ticket_accounts');
        }
      })
        .error(res => {
          if (res.error_message) {
            return this.Growl.error(res.error_message);
          }
      });
    }
  }
  Admin_TicketAccounts_Ctrl_List.initClass();

  return Admin_TicketAccounts_Ctrl_List.EXPORT_CTRL();
});