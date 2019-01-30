/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Main_Ctrl_Nav extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_Nav';
      this.DEPS = ['$timeout'];
    }

    init() {
      const depth = this.$state.current.name.split('.').length;
      if (depth === 1) {
        this.$timeout(() => $('.dp-layout-appnav').find('li').first().find('a').click()
        , 10);
      }
    }
  }
  Admin_Main_Ctrl_Nav.initClass();

  return Admin_Main_Ctrl_Nav.EXPORT_CTRL();
});
