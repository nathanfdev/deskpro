define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerCron_Ctrl_Logs extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerCron_Ctrl_Logs';
      this.CTRL_AS   = 'LogsCtrl';
      this.DEPS      = [];
    }

    init() {
      this.server_cron_logs = null;
      this.page = 1;
      this.num_pages = 0;
      this.page_nums = [1];

      this.filter = {
        job_id:   '',
        priority: '',
        page:     1
      };

      return this.initializeScopeWatching();
    }

    initialLoad() {
      return this.loadResults();
    }

    /*
  *
  */

    loadResults() {
      this.startSpinner('paginating_server_cron_logs');

      const data_promise = this.Api.sendGet('/server_cron/logs', {
        job_id:   this.filter.job_id,
        priority: this.filter.priority,
        page:     this.filter.page
      }).then((res) => {
        this.server_cron_logs = res.data.server_cron_logs;

        this.filter.page = res.data.server_cron_logs.page;
        this.page = res.data.server_cron_logs.page;
        this.num_pages = res.data.server_cron_logs.num_pages;

        this.page_nums = [];

        for (let i = 0, end = this.num_pages, asc = end >= 0; asc ? i < end : i > end; asc ? i++ : i--) {
          this.page_nums.push(i + 1);
        }

        return this.stopSpinner('paginating_server_cron_logs', true);
      });

      return this.$q.all([data_promise]);
    }

    updateFilter() {
      return this.loadResults();
    }

    /*
     * Show the clear dlg
     */

    startClearAll() {
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Server/server-cron-delete-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
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
      return this.Api.sendDelete('/server_cron/logs').success(() => this.server_cron_logs = null);
    }

    /*
     * Here we watching scope 'page' variable in order to load new page of results
     */

    initializeScopeWatching() {
      return this.$scope.$watch('LogsCtrl.page', (newVal, oldVal) => {
        if (parseInt(newVal) === parseInt(oldVal)) {
          return undefined;
        }

        if (isNaN(parseInt(newVal))) {
          return undefined;
        }

        return this.changePageCallback();
      });
    }

    /*
     * This is executed after we chnaged the current page
     */

    changePageCallback() {
      this.filter.page = this.page;
      return this.loadResults();
    }

    /*
  *
    */

    goPrevPage() {
      return this.page--;
    }

    /*
     *
     */

    goNextPage() {
      return this.page++;
    }
  }
  Admin_ServerCron_Ctrl_Logs.initClass();

  return Admin_ServerCron_Ctrl_Logs.EXPORT_CTRL();
});
