/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
      const promise = this.Api.sendGet('/agents/deleted').then( result => {
        return this.agents = result.data.agents;
      });
      return promise;
    }

    removeAgentFromList(id) {
      return this.agents = this.agents.filter(x => x.id !== id);
    }

    updateAgent(agent) {
      return (() => {
        const result = [];
        for (let a of Array.from(this.agents)) {
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