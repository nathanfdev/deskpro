define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Main_Ctrl_GoToUser extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_GoToUser';
    }

    init() {
      if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.reports) {
        return window.location.href = window.DP_BASE_URL;
      }
      window.parent.DP_FRAME_OVERLAYS.user.open();
      return window.parent.DP_FRAME_OVERLAYS.admin.close();
    }
  }
  Admin_Main_Ctrl_GoToUser.initClass();

  return Admin_Main_Ctrl_GoToUser.EXPORT_CTRL();
});
