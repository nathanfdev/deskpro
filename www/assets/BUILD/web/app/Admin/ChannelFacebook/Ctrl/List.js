define(['Admin/Main/Ctrl/Base'], (Admin_Main_Ctrl_Base) => {
  class Admin_ChannelFacebook_Ctrl_List extends Admin_Main_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ChannelFacebook_Ctrl_List';
      this.CTRL_AS = 'ChannelFacebookList';
      this.CTRL_TYPE = 'list';
      this.DEPS = ['FacebookPagesData'];
    }

    init() {
      return this.pages = [];
    }

    initialLoad() {
      const list_promise = this.FacebookPagesData.loadList().then((recs) => {
        this.pages = [];
        let accounts = recs.values();
        for (var acc of Array.from(accounts)) {
          this.pages.push(acc);
        }

        if (this.$state.current.name === 'tickets.channel_facebook') {
          if (this.pages[0]) {
            this.$state.go('tickets.channel_facebook.edit', { id: this.pages[0].id });
          } else {
            this.$state.go('tickets.channel_facebook.create');
          }
        }

        return this.addManagedListener(this.FacebookPagesData.recs, 'changed', () => {
          this.pages = [];
          accounts = this.FacebookPagesData.recs.values();
          for (acc of Array.from(accounts)) {
            acc.phone_number_region = acc.phone_number_region != null ? acc.phone_number_region.toLowerCase() : undefined;
            this.pages.push(acc);
          }
          return this.ngApply();
        });
      });

      return this.$q.all([list_promise]);
    }

    startDelete(for_acc_id) {
      let for_acc = null;
      for (const v of Array.from(this.pages)) {
        if (v.id === for_acc_id) {
          for_acc = v;
        }
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('ChannelFacebook/delete-modal.html'),
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
      return this.Api.sendDelete(`/channel/facebook/page/${acc.id}`).success(() => {
        this.FacebookPagesData.remove(acc.id);
        this.ngApply();

        // if currently viewing the deleted account, then should need to switch state
        if ((this.$state.current.name === 'tickets.channel_facebook.edit') && (parseInt(this.$state.params.id) === acc.id)) {
          return this.$state.go('tickets.channel_facebook');
        }
      });
    }
  }
  Admin_ChannelFacebook_Ctrl_List.initClass();

  return Admin_ChannelFacebook_Ctrl_List.EXPORT_CTRL();
});
