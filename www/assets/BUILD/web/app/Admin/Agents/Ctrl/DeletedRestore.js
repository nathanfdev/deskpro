/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Agents_Ctrl_DeletedRestore extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Agents_Ctrl_DeletedRestore';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['DpLicense'];
    }

    init() {
      this.agentId = parseInt(this.$stateParams.id);
      this.service = this.DataService.get('Agents');
    }

    initialLoad() {
      const promise = this.Api.sendDataGet({
        agent: `/agents/deleted/${this.agentId}`
      });

      promise.then( result => {
        return this.agent = result.data.agent.agent;
      });
      return promise;
    }

    restoreAgent() {
      this.startSpinner('saving');

      const promise = this.Api.sendPost(`/agents/deleted/${this.agentId}/undelete`);
      promise.then( () => {
        this.stopSpinner('saving', true);
        this.service.all(true);
        return this.$state.go('agents.agents.edit', {id: this.agentId});
      }
      , res => {
        this.stopSpinner('saving', true);
        if (res.data.error_code && (res.data.error_code === 'license_exceeded')) {
          return this.DpLicense.openUpgradeLicense('upgrade_plan').then(() => {
            return this.restoreAgent();
          });
        }
      });
      return promise;
    }

    convertToUser() {
      this.startSpinner('saving_convert');
      const promise = this.Api.sendDelete(`/agents/${this.agentId}/delete/to-user`);
      promise.then( () => {
        this.stopSpinner('saving_convert', true);
        return this.$state.go('agents.agents');
      });
      return promise;
    }
  }
  Admin_Agents_Ctrl_DeletedRestore.initClass();

  return Admin_Agents_Ctrl_DeletedRestore.EXPORT_CTRL();
});