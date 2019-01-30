// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS202: Simplify dynamic range loops
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/LocalStore',
  'moment'
], function(
  Admin_Ctrl_Base,
  LocalStore,
  moment) {
  class Admin_EmailStatus_Ctrl_SendmailList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_EmailStatus_Ctrl_SendmailList';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS    = ['DpDateService'];
    }

    init() {
      this.storeFilterId = Admin_EmailStatus_Ctrl_SendmailList.CTRL_ID+'.filter';
      this.filter = {
        page: 1
      };
      this.results = [];
      this.num_results = 0;
      this.num_pages = 0;
      this.page_nums = [1];
      this.filter_date_mode = "none";
      this.page = 1;
      this.massActionsOp = "resend";

      if (LocalStore.has(this.storeFilterId)) {
        this.filter = LocalStore.getObject(this.storeFilterId, this.filter);
        this.filter.page = 1;
        this.$scope.filter_open = true;
      }

      return this.$scope.$watch('ListCtrl.page', (newVal, oldVal) => {
        if (parseInt(newVal) === parseInt(oldVal)) {
          return;
        }
        if (isNaN(parseInt(newVal))) {
          return;
        }

        return this.changePage();
      });
    }

    initialLoad() {
      return this.loadResults();
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
      const promise = this.Api.sendGet('/email_status/sendmail', {filter: this.filter}).success( data => {
        this.stopSpinner('loading_page', true);
        this.$scope.tracking_enabled = data.tracking_enabled;
        this.results     = data.sendmail_queue;
        this.page        = data.page;
        this.num_pages   = data.num_pages;
        this.num_results = data.count;
        this.massActions = {};
        this.massActionsAll = false;
        this.massActionsLoading = false;

        this.page_nums = [];
        for (let i = 0, end = this.num_pages, asc = 0 <= end; asc ? i < end : i > end; asc ? i++ : i--) {
          this.page_nums.push(i+1);
        }

        this.results.map(res => {
          res.date_created = this.DpDateService.local(res.date_created);
          if (res.date_sent) {
            res.date_sent = this.DpDateService.local(res.date_sent);
          }
          if (res.date_next_attempt) {
            return res.date_next_attempt = this.DpDateService.local(res.date_next_attempt);
          }
        });

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
      const url = `/email_status/sendmail/mass-actions/${this.massActionsOp}`;
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
  Admin_EmailStatus_Ctrl_SendmailList.initClass();

  return Admin_EmailStatus_Ctrl_SendmailList.EXPORT_CTRL();
});