// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_CustomFields_Kb_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_CustomFields_Kb_Ctrl_List';
      this.DEPS = [];
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      return this.$scope.fields = [];
    }

    initialLoad() {
      return this.DataService.get('KbFields').loadList().then(list => { return this.$scope.fields = list; });
    }
  }
  Admin_CustomFields_Kb_Ctrl_List.initClass();

  return Admin_CustomFields_Kb_Ctrl_List.EXPORT_CTRL();
});