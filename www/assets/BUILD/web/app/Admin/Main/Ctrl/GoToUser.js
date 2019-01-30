// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Main_Ctrl_GoToUser extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_GoToUser';
    }

    init() {
      if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.reports) {
        return window.location.href = window.DP_BASE_URL;
      } else {
        window.parent.DP_FRAME_OVERLAYS.user.open();
        return window.parent.DP_FRAME_OVERLAYS.admin.close();
      }
    }
  }
  Admin_Main_Ctrl_GoToUser.initClass();

  return Admin_Main_Ctrl_GoToUser.EXPORT_CTRL();
});