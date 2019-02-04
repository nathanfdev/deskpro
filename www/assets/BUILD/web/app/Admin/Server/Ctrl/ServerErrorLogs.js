define(['underscore', 'Admin/Main/Ctrl/Base'], (_, Admin_Ctrl_Base) => {
  class Admin_ServerErrorLogs_Ctrl_ServerErrorLogs extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerErrorLogs_Ctrl_ServerErrorLogs';
      this.CTRL_AS   = 'ServerErrorLogs';
      this.DEPS      = [];
    }

    init() {
      this.$scope.server_error_logs = null;
      this.$scope.logs_size = 0;
      return this.logs_path = '';
    }

    initialLoad() {
      const data_promise = this.Api.sendGet('/server_error_logs').then((res) => {
        this.logs_path = res.data.path;
        this.$scope.server_error_logs = res.data.server_error_logs;
        if (this.$scope.server_error_logs != null ? this.$scope.server_error_logs.logs : undefined) {
          this.$scope.server_error_logs.logs = this.$scope.server_error_logs.logs.reverse();
        }
        return this.$scope.logs_size = _.size(this.$scope.server_error_logs.logs);
      });

      return this.$q.all([data_promise]);
    }

    /*
     * Show the clear dlg
     */
    startClearAll() {
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Server/server-error-logs-delete-modal.html'),
        controller:  ['$scope', '$modalInstance',  function ($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => this.clearAll());
    }

    /*
     * Actually do the clear
     */
    clearAll() {
      return this.Api.sendDelete('/server_error_logs').success(() => this.$scope.server_error_logs.logs = null).error(() => this.showAlert(`Clearing the logs failed because the log file is not writable by the web server. You must make the ${this.logs_path}/error.log file writable before you can clear it.`));
    }
  }
  Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.initClass();

  return Admin_ServerErrorLogs_Ctrl_ServerErrorLogs.EXPORT_CTRL();
});
