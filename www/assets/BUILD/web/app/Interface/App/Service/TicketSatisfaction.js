// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS102: Remove unnecessary code created because of implicit returns
 * DS202: Simplify dynamic range loops
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Reports/App/Service/BaseService',
  'moment',
], function(
  BaseService,
  moment,
) {
  class TicketSatisfaction extends BaseService {
    constructor(Api, $sce, $q, $timeout) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.Api = Api;
      this.$sce = $sce;
      this.$q = $q;
      this.$timeout = $timeout;
      this.feed_html = '';
      this.summary_html = '';
      this.page_nums = [1];
      this.num_pages = 0;
      this.page = 1;
      this.view_date = moment(this.date).format("YYYY-MM");
      this.dp_spin_els = {};
    }

    loadFeedResults() {
      this.startSpinner('loading_feed_results');

      const promise = this.Api.sendGet(`/reports/ticket-satisfaction/${this.page}`).then(res => {
        this.feed_html = this.$sce.trustAsHtml(res.data.html);

        this.page = res.data.page || 1;
        this.num_pages = res.data.num_pages || 1;

        this.page_nums = [];

        for (let i = 0, end = this.num_pages, asc = 0 <= end; asc ? i < end : i > end; asc ? i++ : i--) {
          this.page_nums.push(i + 1);
        }

        return this.stopSpinner('loading_feed_results', true);
      });

      return promise;
    }

    loadSummaryResults() {
      this.startSpinner('loading_summary_results');

      const promise = this.Api.sendGet(`/reports/ticket-satisfaction/summary/${this.view_date}`).then(res => {
        this.summary_html = this.$sce.trustAsHtml(res.data.html);
        return this.stopSpinner('loading_summary_results', true);
      });

      return promise;
    }

    changePage() {
      return this.loadFeedResults();
    }

    goPrevPage() {
      this.page--;
      return this.changePage();
    }

    goNextPage() {
      this.page++;
      return this.changePage();
    }
  }
  return TicketSatisfaction;
});