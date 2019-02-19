define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_TicketStatuses_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketStatuses_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS = [];
    }

    init() {
      this.statusData = this.DataService.get('TicketStatuses');
      this.status = null;
      this.form   = {};
      // Statuses translations
      this.statusTrans = {};
    }

    initialLoad() {
      if (this.$stateParams.id) {
        return this.statusData.loadEditStatusData(this.$stateParams.id).then(
          (data) => {
            this.status = data.status;
            this.form = {
              title: this.status.title
            };
          });
      }
      this.status = {};
      this.form = {
        title:       '',
        status_type: 'awaiting_agent'
      };
    }

    saveForm() {
      let is_new,
        promise;
      const postData = {
        title: this.form.title
      };
      if (!this.status.id) {
        postData.status_type = this.form.status_type;
      }

      this.startSpinner('saving');
      if (this.status.id) {
        is_new = false;
        promise = this.Api2.sendPutJson(`/ticket_statuses/${this.status.id}`, postData);
      } else {
        is_new = true;
        promise = this.Api2.sendPostJson('/ticket_statuses', postData);
      }

      promise.success((result) => {
        if (!this.status.id) {
          this.status.id = result.data.id;
          this.status.status_type = this.form.status_type;
        }
        this.status.title = this.form.title;

        this.stopSpinner('saving', true).then(() => this.Growl.success('Saved'));

        this.statusData.mergeDataModel({
          id:          this.status.id,
          title:       this.status.title,
          status_type: this.status.status_type
        });

        this.skipDirtyState();
        if (is_new) {
          return this.$state.go('tickets.statuses.edit', { id: this.status.id });
        }
      });
      promise.error((info, code) => {
        this.stopSpinner('saving', true);
        this.applyErrorResponseToView(info);
        if (__guard__(info != null ? info.errors : undefined, x => x.errors)) {
          return this.Growl.error(info.errors.errors[0].message);
        }
      });

      return promise;
    }

    startDelete() {
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketStatuses/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', ($scope, $modalInstance) => {
          $scope.status = this.statusTrans[this.status.status_type];
          $scope.confirm = () => $modalInstance.close();

          $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => this.statusData.deleteStatusById(this.status.id).then(() => {
        if ((this.$state.current.name === 'tickets.statuses.edit') && (parseInt(this.$state.params.id) === this.status.id)) {
          return this.$state.go('tickets.statuses');
        }
      }));
    }
  }
  Admin_TicketStatuses_Ctrl_Edit.initClass();

  return Admin_TicketStatuses_Ctrl_Edit.EXPORT_CTRL();
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}
