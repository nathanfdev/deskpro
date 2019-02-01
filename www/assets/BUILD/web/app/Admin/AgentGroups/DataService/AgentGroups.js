define([
  'Admin/Main/DataService/BaseListEdit'
], (
  Admin_Main_DataService_BaseListEdit
) => {
  class Admin_AgentGroups_DataService_AgentGroups extends Admin_Main_DataService_BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    url() { return 'agent_groups'; }

    resolveResponse(response) { return response.groups; }

    all() {
      return super.all(false, { with_perms: 1 });
    }
  }
  Admin_AgentGroups_DataService_AgentGroups.initClass();
  return Admin_AgentGroups_DataService_AgentGroups;
});
