// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Templates_Ctrl_EmailGroupListOld extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Templates_Ctrl_EmailGroupListOld';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    initialLoad() {
      const promise = this.Api.sendDataGet({
        info: '/email-templates-info'
      }).then( res => {
        return this.templateInfo = res.data.info.list;
      });
      return promise;
    }
  }
  Admin_Templates_Ctrl_EmailGroupListOld.initClass();

  return Admin_Templates_Ctrl_EmailGroupListOld.EXPORT_CTRL();
});