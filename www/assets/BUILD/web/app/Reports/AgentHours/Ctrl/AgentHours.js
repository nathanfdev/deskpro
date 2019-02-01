define([
  'Reports/Main/Ctrl/Base',
  'moment',
], function(
  ReportsBaseCtrl,
  moment,
) {
  class Reports_AgentHours_Ctrl_AgentHours extends ReportsBaseCtrl {
    static initClass() {
      this.CTRL_ID   = 'Reports_AgentHours_Ctrl_AgentHours';
      this.CTRL_AS   = 'AgentHours';
      this.DEPS      = ['Api', '$sce'];
    }


    /*
     * Initializing..
     */
    init() {
      this.html = '';
      this.date1 = new Date();
      this.date2 = new Date();
      this.filter = {};
      this.filter.date1 = moment(this.date).format("YYYY-MM-DD");
      return this.filter.date2 = moment(this.date).format("YYYY-MM-DD");
    }


    /*
     * Just doing all the necessary AJAX calls here
     */
    initialLoad() {
      return this.loadResults();
    }


    /*
     * This method updates current parameters that are used for sending request to API
     */
    updateFilter() {
      this.filter.date1 = moment(this.date1).format("YYYY-MM-DD");
      this.filter.date2 = moment(this.date2).format("YYYY-MM-DD");
      return this.loadResults();
    }


    /*
     * Loading the results of sending request to API
     */
    loadResults() {
      this.startSpinner('loading_results');

      const promise = this.Api.sendGet(`/reports/agent-hours/${this.filter.date1}/${this.filter.date2}`).then(res => {
        this.html = this.$sce.trustAsHtml(res.data.html);

        return this.stopSpinner('loading_results', true);
      });

      return promise;
    }
  }
  Reports_AgentHours_Ctrl_AgentHours.initClass();


  return Reports_AgentHours_Ctrl_AgentHours.EXPORT_CTRL();
});