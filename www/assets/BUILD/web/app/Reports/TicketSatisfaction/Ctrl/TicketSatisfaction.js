define([
  'Reports/Main/Ctrl/Base',
  'moment'
], (
  ReportsBaseCtrl,
  moment
) => {
  class Reports_TicketSatisfaction_Ctrl_TicketSatisfaction extends ReportsBaseCtrl {
    static initClass() {
      this.CTRL_ID   = 'Reports_TicketSatisfaction_Ctrl_TicketSatisfaction';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['Api', '$sce'];

      Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.EXPORT_CTRL();
    }

    init() {
      this.feed_html = '';
      this.summary_html = '';
      this.page_nums = [1];
      this.num_pages = 0;
      this.page = 1;
      this.$scope.view_date = moment(this.date).format('YYYY-MM');
      return this.$scope.mode = 'feed';
    }

    initialLoad() {
      return this.loadFeedResults();
    }

    switchToFeed() {
      this.$scope.mode = 'feed';
      return this.loadFeedResults();
    }

    switchToSummary() {
      this.$scope.mode = 'summary';
      return this.loadSummaryResults();
    }

    loadFeedResults() {
      this.startSpinner('loading_feed_results');

      const promise = this.Api.sendGet(`/reports/ticket-satisfaction/${this.page}`).then((res) => {
        this.feed_html = this.$sce.trustAsHtml(res.data.html);

        this.page = res.data.page || 1;
        this.num_pages = res.data.num_pages || 1;

        this.page_nums = [];

        for (let i = 0, end = this.num_pages, asc = end >= 0; asc ? i < end : i > end; asc ? i++ : i--) {
          this.page_nums.push(i + 1);
        }

        return this.stopSpinner('loading_feed_results', true);
      });

      return promise;
    }

    loadSummaryResults() {
      this.startSpinner('loading_summary_results');

      const promise = this.Api.sendGet(`/reports/ticket-satisfaction/summary/${this.$scope.view_date}`).then((res) => {
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
  Reports_TicketSatisfaction_Ctrl_TicketSatisfaction.initClass();
  return Reports_TicketSatisfaction_Ctrl_TicketSatisfaction;
});
