// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Portal_Ctrl_Nav extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Portal_Ctrl_Nav';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$timeout', '$state', '$stateParams'];
    }

    init() {
      const depth = this.$state.current.name.split('.').length;
      if (depth === 1) {
        this.$timeout(() => $('.dp-layout-appnav').find('li').first().find('a').first().click()
        , 10);
        return;
      }
    }
  }
  Admin_Portal_Ctrl_Nav.initClass();


  return Admin_Portal_Ctrl_Nav.EXPORT_CTRL();
});
