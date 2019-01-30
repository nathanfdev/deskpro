/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
], function(
  Admin_Main_DataService_BaseListEdit,
)  {
  let Admin_AgentTeams_DataService_AgentTeams;
  return Admin_AgentTeams_DataService_AgentTeams = (function() {
    Admin_AgentTeams_DataService_AgentTeams = class Admin_AgentTeams_DataService_AgentTeams extends Admin_Main_DataService_BaseListEdit {
      static initClass() {
        this.$inject = ['Api', '$q'];
      }


      url() { return '/agent_teams'; }

      resolveResponse(response) { return response.agent_teams; }
    };
    Admin_AgentTeams_DataService_AgentTeams.initClass();
    return Admin_AgentTeams_DataService_AgentTeams;
  })();
});
