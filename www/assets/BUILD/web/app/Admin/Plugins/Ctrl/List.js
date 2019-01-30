/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Plugins_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Plugins_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      return this.packages = [];
    }

    initialLoad() {
      const promise = this.Api.sendGet('/plugins/packages').then( result => {
        return this.packages = result.data.packages;
      });
      return promise;
    }
  }
  Admin_Plugins_Ctrl_List.initClass();

  return Admin_Plugins_Ctrl_List.EXPORT_CTRL();
});