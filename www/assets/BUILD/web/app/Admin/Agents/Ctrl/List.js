define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_Agents_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Agents_Ctrl_List';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    init() {
      this.service =
        { agents: this.DataService.get('Agents') };
    }

    initialLoad() {
      this.service.agents.all().then(agents => this.agents = agents);

      const promise = this.Api.sendDataGet({
        deleted_agents: '/agents/deleted'
      }).then(result => this.deletedCount = result.data.deleted_agents.agents.length);
      return promise;
    }

    removeAgentFromList(id) {
      return this.deletedCount++;
    }
  }
  Admin_Agents_Ctrl_List.initClass();

  return Admin_Agents_Ctrl_List.EXPORT_CTRL();
});
