define(['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) => {
  class Admin_AgentTeams_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_AgentTeams_Ctrl_List';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    init() {
    }

    initialLoad() {
      const promise = this.Api.sendGet('/agent_teams').then(result => this.teams = result.data.agent_teams);
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
