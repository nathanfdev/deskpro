define([
  'Admin/Main/DataService/BaseListEdit'
], (
  Admin_Main_DataService_BaseListEdit
) => {
  class Admin_AgentTeams_DataService_AgentTeams extends Admin_Main_DataService_BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }


    url() { return '/agent_teams'; }

    resolveResponse(response) { return response.agent_teams; }
  }
  Admin_AgentTeams_DataService_AgentTeams.initClass();
  return Admin_AgentTeams_DataService_AgentTeams;
});
