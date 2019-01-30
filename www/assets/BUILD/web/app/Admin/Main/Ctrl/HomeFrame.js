/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Strings'
], function(
  Admin_Ctrl_Base,
  Strings
) {
  class Admin_Main_Ctrl_HomeFrame extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_HomeFrame';
      this.CTRL_AS   = 'Home';
      this.DEPS      = ['$http', 'DpLicense', 'Growl'];
    }

    init() {
      return this.$scope.iframe_src = window.ADMIN_DASH_IFRAME_SRC;
    }
  }
  Admin_Main_Ctrl_HomeFrame.initClass();

  return Admin_Main_Ctrl_HomeFrame.EXPORT_CTRL();
});
