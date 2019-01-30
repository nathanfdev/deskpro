/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_AgentGroups_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_AgentGroups_Ctrl_List';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    init() {
    }

    initialLoad() {
      return this.DataService.get('AgentGroups').all().then(groups => { return this.groups = groups; });
    }
  }
  Admin_AgentGroups_Ctrl_List.initClass();

  return Admin_AgentGroups_Ctrl_List.EXPORT_CTRL();
});