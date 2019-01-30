// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Main_Ctrl_GoToReports extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_GoToReports';
    }

    init() {
      if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.reports) {
        return window.location.href = window.DP_BASE_URL + 'reports/';
      } else {
        window.parent.DP_FRAME_OVERLAYS.reports.open();
        return window.parent.DP_FRAME_OVERLAYS.admin.close();
      }
    }
  }
  Admin_Main_Ctrl_GoToReports.initClass();

  return Admin_Main_Ctrl_GoToReports.EXPORT_CTRL();
});