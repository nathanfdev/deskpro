// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Reports/Main/Ctrl/Base'], function(Reports_Main_Ctrl_Bare) {
  class Reports_Main_Ctrl_GoToAdmin extends Reports_Main_Ctrl_Bare {
    static initClass() {
      this.CTRL_ID   = 'Reports_Main_Ctrl_GoToAdmin';
      this.DEPS      = ['$location'];
    }

    init() {
      if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.admin) {
        return window.location.href = window.DP_BASE_URL + 'admin/';
      } else {
        window.parent.DP_FRAME_OVERLAYS.admin.open();
        return window.parent.DP_FRAME_OVERLAYS.reports.close();
      }
    }
  }
  Reports_Main_Ctrl_GoToAdmin.initClass();

  return Reports_Main_Ctrl_GoToAdmin.EXPORT_CTRL();
});
