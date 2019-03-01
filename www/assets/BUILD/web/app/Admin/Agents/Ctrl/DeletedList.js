define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Agents_Ctrl_DeletedList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Agents_Ctrl_DeletedList';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    init() {
    }

    initialLoad() {
      const promise = this.Api.sendGet('/agents/deleted').then(result => this.agents = result.data.agents);
      return promise;
    }

    removeAgentFromList(id) {
      return this.agents = this.agents.filter(x => x.id !== id);
    }

    updateAgent(agent) {
      return (() => {
        const result = [];
        for (const a of Array.from(this.agents)) {
          if (a.id === agent.id) {
            result.push(a.display_name = agent.display_name);
          } else {
            result.push(undefined);
          }
        }
        return result;
      })();
    }

    addAgent(id, name) {
      return this.agents.push({
        id,
        display_name: name
      });
    }
  }
  Admin_Agents_Ctrl_DeletedList.initClass();

  return Admin_Agents_Ctrl_DeletedList.EXPORT_CTRL();
});
