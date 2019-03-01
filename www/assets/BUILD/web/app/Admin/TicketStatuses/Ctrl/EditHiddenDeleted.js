define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketStatuses_Ctrl_EditHiddenDeleted extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenDeleted';
      this.CTRL_AS = 'TicketStatusEdit';
      this.DEPS = [];
    }

    init() {
      this.$scope.getCount = () => (this.$scope.$parent.TicketStatusesList != null ? this.$scope.$parent.TicketStatusesList.getStatusCount('hidden_deleted') : undefined);
      this.$scope.settings = {
        auto_purge_time: 604800
      };
      this.$scope.times = [
        { id: 86400, label: '1 day' },
        { id: 259200, label: '3 days' },
        { id: 432000, label: '5 days' },
        { id: 604800, label: '1 week' },
        { id: 1209600, label: '2 weeks' },
        { id: 1814400, label: '3 weeks' },
        { id: 2592000, label: '1 month' },
        { id: 5184000, label: '2 months' },
        { id: 7776000, label: '3 months' },
        { id: 15552000, label: '6 months' },
        { id: 23328000, label: '9 months' },
        { id: 31536000, label: '1 year' },
        { id: 63072000, label: '2 years' }
      ];
    }

    initialLoad() {
      const promise = this.Api.sendGet('/ticket_statuses/deleted').success(data => this.$scope.settings.auto_purge_time = data.deleted_info.auto_purge_time);

      return promise;
    }

    saveSettings() {
      this.startSpinner('saving_settings');
      const promise = this.Api.sendPostJson('/ticket_statuses/deleted/settings', this.$scope.settings).then(() => this.stopSpinner('saving_settings'));

      return promise;
    }

    startPurge() {
      let inst;
      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketStatuses/modal-purge-deleted.html'),
        controller:  ['$scope', '$modalInstance', 'Api', ($scope, $modalInstance, Api) => {
          let purgeNow;
          $scope.dismiss = () => $modalInstance.dismiss();

          $scope.confirm = () => purgeNow();

          return purgeNow = function () {
            $scope.is_loading = true;
            return Api.sendDelete('/ticket_statuses/deleted/purge').success((data) => {
              $scope.is_done = true;
              return $scope.count = data.count;
            }).then((() => $scope.is_loading = false)
            );
          };
        }
        ]
      });
    }
  }
  Admin_TicketStatuses_Ctrl_EditHiddenDeleted.initClass();

  return Admin_TicketStatuses_Ctrl_EditHiddenDeleted.EXPORT_CTRL();
});
