define(['Reports/Main/Ctrl/Base'], function(Reports_Main_Ctrl_Bare) {
  class Reports_Main_Ctrl_GoToBilling extends Reports_Main_Ctrl_Bare {
    static initClass() {
      this.CTRL_ID   = 'Reports_Main_Ctrl_GoToBilling';
      this.DEPS      = ['$location'];
    }

    init() {
      if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.admin) {
        return window.location.href = window.DP_BASE_URL + 'admin#/license';
      } else {
        window.parent.DP_FRAME_OVERLAYS.admin.open('/license');
        return window.parent.DP_FRAME_OVERLAYS.reports.close();
      }
    }
  }
  Reports_Main_Ctrl_GoToBilling.initClass();

  return Reports_Main_Ctrl_GoToBilling.EXPORT_CTRL();
});
