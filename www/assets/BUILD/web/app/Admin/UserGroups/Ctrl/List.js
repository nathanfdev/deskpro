// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_UserGroups_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_UserGroups_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS = [];
    }

    init() {
      return this.ugData = this.DataService.get('UserGroups');
    }

    initialLoad() {
      const promise = this.ugData.loadList().then( list => {
        return this.list = list;
      });

      return promise;
    }
  }
  Admin_UserGroups_Ctrl_List.initClass();

  return Admin_UserGroups_Ctrl_List.EXPORT_CTRL();
});