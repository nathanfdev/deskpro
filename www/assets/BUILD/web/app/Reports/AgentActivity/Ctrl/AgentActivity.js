define([
  'Reports/Main/Ctrl/Base',
  'moment',
], function(
  ReportsBaseCtrl,
  moment,
) {
  class Reports_AgentActivity_Ctrl_AgentActivity extends ReportsBaseCtrl {
    static initClass() {
      this.CTRL_ID   = 'Reports_AgentActivity_Ctrl_AgentActivity';
      this.CTRL_AS   = 'AgentActivity';
      this.DEPS      = ['Api', '$sce'];
    }


    /*
     * Initializing..
     */
    init() {
      this.html = '';
      this.all_agents = [];
      this.agent_teams = [];
      this.filter = {};
      this.filter.date = new Date();
      this.filter.agent_or_team = '0';

      this.$scope.$watch('AgentActivity.filter.agent_or_team', () => {
        return this.html = '';
      });
      return this.$scope.$watch('AgentActivity.filter.date', () => {
        return this.html = '';
      });
    }


    /*
     * Just doing all the necessary AJAX calls here
     */
    initialLoad() {
      const date = moment(this.filter.date).format("YYYY-MM-DD");
      return this.Api.sendGet("/reports/agent-activity/0/${date}").then(res => {
        this.html = this.$sce.trustAsHtml(res.data.html);
        this.all_agents = res.data.all_agents;
        return this.agent_teams = res.data.agent_teams;
      });
    }


    /*
     * This method updates current parameters that are used for sending request to API
     */
    updateFilter() {
      if (!this.filter.date) { this.filter.date = new Date(); }
      return this.loadResults();
    }


    /*
     * Loading the results of sending request to API
     */
    loadResults() {
      this.startSpinner('loading_results');
      const date = moment(this.filter.date).format("YYYY-MM-DD");
      return this.Api.sendGet(`/reports/agent-activity/${this.filter.agent_or_team}/${date}`).then(res => {
        this.html = this.$sce.trustAsHtml(res.data.html);
        return this.stopSpinner('loading_results', true);
      });
    }
  }
  Reports_AgentActivity_Ctrl_AgentActivity.initClass();



  return Reports_AgentActivity_Ctrl_AgentActivity.EXPORT_CTRL();
});