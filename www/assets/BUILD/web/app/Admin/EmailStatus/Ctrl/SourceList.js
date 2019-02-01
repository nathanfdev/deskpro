define([
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/LocalStore',
  'moment'
], function(
  Admin_Ctrl_Base,
  LocalStore,
  moment) {
  class Admin_EmailStatus_Ctrl_SourceList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_EmailStatus_Ctrl_SourceList';
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      this.storeFilterId = Admin_EmailStatus_Ctrl_SourceList.CTRL_ID+'.filter';
      this.filter = {
        account: "0",
        page: 1
      };
      this.results = [];
      this.num_results = 0;
      this.num_pages = 0;
      this.page_nums = [1];
      this.filter_date_mode = "none";
      this.page = 1;
      this.massActionsOp = "reprocess";

      if (LocalStore.has(this.storeFilterId)) {
        this.filter = LocalStore.getObject(this.storeFilterId, this.filter);
        this.filter.page = 1;
        this.$scope.filter_open = true;
      }

      this.$scope.$watch('ListCtrl.page', (newVal, oldVal) => {
        if (parseInt(newVal) === parseInt(oldVal)) {
          return;
        }
        if (isNaN(parseInt(newVal))) {
          return;
        }

        return this.changePage();
      });

      return this.$scope.showStatusHelp = () => {
        let modalInstance;
        return modalInstance = this.$modal.open({
          templateUrl: this.getTemplatePath('EmailStatus/emailsource-status-code-modal.html'),
          controller: ['$scope', '$modalInstance', ($scope, $modalInstance) =>
            $scope.dismiss = () => $modalInstance.dismiss()
          
          ]
        });
      };
    }

    initialLoad() {
      const p1 = this.loadResults();
      const p2 = this.Api.sendGet('/email_accounts').success( data => {
        return this.$scope.email_accounts = data.email_accounts;
      });
      this.Api.sendGet('/email_status/stats').success( data => {
        return this.$scope.counts = {
          status: data.by_status || {},
          account: data.by_account || {}
        };
      });

      return this.$q.all([p1, p2]);
    }

    changePage() {
      if (this.filter.page === this.page) {
        return;
      }

      this.filter.page = this.page;
      return this.loadResults(true);
    }

    clearFilter() {
      this.filter = {
        account: "0",
        page: 1
      };
      this.$scope.filter_open = false;
      this.updateFilter(true);
      return LocalStore.remove(this.storeFilterId);
    }

    updateFilter(skipSave) {
      this.page = 1;
      this.filter.page = this.page;

      this.filter.date_start = null;
      this.filter.date_end = null;
      if (this.filter_date_mode && (this.filter_date_mode !== 'none')) {
        if (this.filter_date1 && ((this.filter_date_mode === 'between') || (this.filter_date_mode === 'after'))) {
          this.filter.date_start = moment(this.filter_date1).format("YYYY-MM-DD");
        }
        if (this.filter_date2 && ((this.filter_date_mode === 'between') || (this.filter_date_mode === 'before'))) {
          this.filter.date_end = moment(this.filter_date2).format("YYYY-MM-DD");
        }
      }

      if (!skipSave) {
        LocalStore.setObject(this.storeFilterId, this.filter);
      }

      return this.loadResults();
    }

    loadResults(fallbackPrevPage) {
      this.startSpinner('loading_page');
      this.results = [];
      const promise = this.Api.sendGet('/email_status/sources', {filter: this.filter}).success( data => {
        this.stopSpinner('loading_page', true);
        this.results     = data.email_sources;
        this.filter.page = data.page;
        this.page        = data.page;
        this.num_pages   = data.num_pages;
        this.num_results = data.count;
        this.massActions = {};
        this.massActionsAll = false;
        this.massActionsLoading = false;

        this.page_nums = [];
        for (let i = 1, end = this.num_pages, asc = 1 <= end; asc ? i <= end : i >= end; asc ? i++ : i--) {
          this.page_nums.push(i);
        }

        if (fallbackPrevPage && !this.results.length && (data.page > 1)) {
          this.filter.page = data.page - 1;
          return this.loadResults();
        }
      });

      return promise;
    }

    toggleMassActions() {
      this.massActions = {};
      if (this.massActionsAll) {
        return Array.from(this.results).map((r) =>
          (this.massActions[r.id] = true));
      }
    }

    hasAnyMassActions() {
      for (let r of Array.from(this.results)) {
        if (this.massActions[r.id]) { return true; }
      }
      return false;
    }

    performMassActions() {
      const url = `/email_status/sources/mass-actions/${this.massActionsOp}`;
      this.massActionsLoading = true;

      const ids = [];
      for (let r of Array.from(this.results)) {
        if (this.massActions[r.id]) { ids.push(r.id); }
      }

      return this.Api.sendPostJson(url, { ids }).then(() => {
        this.Growl.success(this.getRegisteredMessage(`${this.massActionsOp}_done`));
        return this.loadResults(true);
      });
    }

    goPrevPage() {
      return this.page--;
    }

    goNextPage() {
      return this.page++;
    }
  }
  Admin_EmailStatus_Ctrl_SourceList.initClass();

  return Admin_EmailStatus_Ctrl_SourceList.EXPORT_CTRL();
});