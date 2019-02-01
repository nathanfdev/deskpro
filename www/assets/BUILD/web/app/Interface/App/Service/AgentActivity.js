define([
  'moment',
], function(
  moment,
) {
  class AgentActivity {
    constructor(Api, $sce) {
      this.Api = Api;
      this.$sce = $sce;
      this.html = '';
      this.date = new Date();
      this.all_agents = [];
      this.agent_teams = [];
      this.filter = {};
      this.filter.date = moment(this.date).format("YYYY-MM-DD");
      this.filter.agent_or_team = 'all';
    }


    /*
     * This method updates current parameters that are used for sending request to API
     */
    updateFilter() {
      this.filter.date = moment(this.date).format("YYYY-MM-DD");
      return this.loadResults();
    }


    /*
     * Loading the results of sending request to API
     */
    loadResults() {
//      @startSpinner('loading_results')

      const promise = this.Api.sendGet(`/reports/agent-activity/${this.filter.agent_or_team}/${this.filter.date}`).then(res => {
        this.html = this.$sce.trustAsHtml(res.data.html);
        this.all_agents = res.data.all_agents;
        return this.agent_teams = res.data.agent_teams;

//        @stopSpinner('loading_results', true)
      });

      return promise;
    }
  }
  return AgentActivity;
});