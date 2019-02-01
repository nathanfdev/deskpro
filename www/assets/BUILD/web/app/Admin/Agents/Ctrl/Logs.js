define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Agents_Ctrl_Logs extends Admin_Ctrl_Base {
    static initClass() {
  
      this.CTRL_ID   = 'Admin_Agents_Ctrl_Logs';
      this.CTRL_AS   = 'LogsCtrl';
      this.DEPS      = [];
    }

    init() {
      this.login_logs = null;
      this.page = 1;
      this.num_pages = 0;
      this.page_nums = [1];

      return this.initializeScopeWatching();
    }

    initialLoad() {

      return this.loadResults();
    }

    /*
  *
  */

    loadResults() {

      this.startSpinner('paginating_login_logs');

      const data_promise = this.Api.sendGet('/login_logs', {
        page: this.page
      }).then(res => {

        this.login_logs = res.data.login_logs;
        this.num_pages = res.data.login_logs.num_pages;

        this.page_nums = [];

        for (let i = 0, end = this.num_pages, asc = 0 <= end; asc ? i < end : i > end; asc ? i++ : i--) {
          this.page_nums.push(i + 1);
        }

        return this.stopSpinner('paginating_login_logs', true);
      });

      return this.$q.all([data_promise]);
    }

    /*
  *
  */

    updateFilter() {

      return this.loadResults();
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
     * This is executed after we changed the current page
     */

    changePageCallback() {

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
  Admin_Agents_Ctrl_Logs.initClass();

  return Admin_Agents_Ctrl_Logs.EXPORT_CTRL();
});