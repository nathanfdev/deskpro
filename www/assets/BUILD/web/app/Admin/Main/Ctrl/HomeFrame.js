define([
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Strings'
], (
  Admin_Ctrl_Base,
  Strings
) => {
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
