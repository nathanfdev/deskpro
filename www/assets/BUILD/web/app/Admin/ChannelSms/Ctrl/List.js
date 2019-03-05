define(['Admin/Main/Ctrl/Base'], function(Admin_Main_Ctrl_Base) {
  class Admin_ChannelSms_Ctrl_List extends Admin_Main_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ChannelSms_Ctrl_List';
      this.CTRL_AS = 'ChannelSmsList';
      this.CTRL_TYPE = 'list';
      this.DEPS = ['SmsAccountsData'];
    }

    init() {
      return this.accounts = [];
    }

    initialLoad() {
      const list_promise = this.SmsAccountsData.loadList().then((recs) => {
        this.accounts = [];
        let accounts = recs.values();
        for (var acc of Array.from(accounts)) {
          acc.phone_number_region = acc.phone_number_region != null ? acc.phone_number_region.toLowerCase() : undefined;
          this.accounts.push(acc);
        }


        if (this.$state.current.name === 'tickets.channel_sms') {
          if (this.accounts[0]) {
            this.$state.go('tickets.channel_sms.edit', { id: this.accounts[0].id });
          } else {
            this.$state.go('tickets.channel_sms.create');
          }
        }

        return this.addManagedListener(this.SmsAccountsData.recs, 'changed', () => {
          this.accounts = [];
          accounts = this.SmsAccountsData.recs.values();
          for (acc of Array.from(accounts)) {
            acc.phone_number_region = acc.phone_number_region != null ? acc.phone_number_region.toLowerCase() : undefined;
            this.accounts.push(acc);
          }
          return this.ngApply();
        });
      });

      return this.$q.all([list_promise]);
    }

    startDelete(for_acc_id) {
      let for_acc = null;
      for (const v of Array.from(this.accounts)) {
        if (v.id === for_acc_id) {
          for_acc = v;
        }
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('ChannelSms/delete-modal.html'),
        controller:  [
          '$scope', '$modalInstance', function ($scope, $modalInstance) {
            $scope.confirm = () => $modalInstance.close();

            return $scope.dismiss = () => $modalInstance.dismiss();
          }
        ]
      });

      return inst.result.then(() => this.deleteAccount(for_acc));
    }

    deleteAccount(acc) {
      return this.Api.sendDelete(`/channel/sms/account/${acc.id}`).success(() => {
        this.SmsAccountsData.remove(acc.id);
        this.ngApply();

        // if currently viewing the deleted account, then should need to switch state
        if ((this.$state.current.name === 'tickets.channel_sms.edit') && (parseInt(this.$state.params.id) === acc.id)) {
          return this.$state.go('tickets.channel_sms');
        }
      });
    }
  }
  Admin_ChannelSms_Ctrl_List.initClass();

  return Admin_ChannelSms_Ctrl_List.EXPORT_CTRL();
});
