// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_AgentTeams_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_AgentTeams_Ctrl_List';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    init() {
    }

    initialLoad() {
      const promise = this.Api.sendGet('/agent_teams').then( result => {
        return this.teams = result.data.agent_teams;
      });
      return promise;
    }

    addTeam(team) {
      return this.teams.push(team);
    }

    removeTeamById(teamId) {
      teamId = parseInt(teamId);
      return this.teams = this.teams.filter(x => x.id !== teamId);
    }

    renameTeamById(teamId, name) {
      return this.teams.filter(x => x.id === teamId).map(x => x.name = name);
    }
  }
  Admin_AgentTeams_Ctrl_List.initClass();

  return Admin_AgentTeams_Ctrl_List.EXPORT_CTRL();
});