define(['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) => {
  class Admin_AgentGroups_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_AgentGroups_Ctrl_List';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    init() {
    }

    initialLoad() {
      return this.DataService.get('AgentGroups').all().then(groups => this.groups = groups);
    }
  }
  Admin_AgentGroups_Ctrl_List.initClass();

  return Admin_AgentGroups_Ctrl_List.EXPORT_CTRL();
});
