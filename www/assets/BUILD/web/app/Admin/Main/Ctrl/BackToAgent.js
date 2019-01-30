// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Main_Ctrl_BackToAgent extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_BackToAgent';
      this.DEPS      = ['$location'];
    }

    init() {
      // Redirect back to agent
      // Unless this is in an iframe, in which case simply viewing
      // this route will cause the parent to close the iframe and
      // make the agent interface visible again
      const route = window.location.href.match(/#[^#]+$/)[0].substring(1);
      if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.admin) {
        if (route) {
          return window.location.href = window.DP_BASE_URL + decodeURIComponent(route);
        } else {
          return window.location.href = window.DP_BASE_URL + 'agent/';
        }
      } else {
        window.parent.DP_FRAME_OVERLAYS.admin.close();
        if (route) {
          return window.parent.DeskPRO_Window.loadPage(window.DP_BASE_URL + decodeURIComponent(route), {focus: true, noToggle: true});
        }
      }
    }
  }
  Admin_Main_Ctrl_BackToAgent.initClass();

  return Admin_Main_Ctrl_BackToAgent.EXPORT_CTRL();
});
