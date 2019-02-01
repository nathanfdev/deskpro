define(['Reports/Main/Ctrl/Base'], (Reports_Main_Ctrl_Bare) => {
  class Reports_Main_Ctrl_GoToUser extends Reports_Main_Ctrl_Bare {
    static initClass() {
      this.CTRL_ID   = 'Reports_Main_Ctrl_GoToUser';
    }

    init() {
      if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.admin) {
        return window.location.href = window.DP_BASE_URL;
      }
      window.parent.DP_FRAME_OVERLAYS.user.open();
      return window.parent.DP_FRAME_OVERLAYS.reports.close();
    }
  }
  Reports_Main_Ctrl_GoToUser.initClass();

  return Reports_Main_Ctrl_GoToUser.EXPORT_CTRL();
});
