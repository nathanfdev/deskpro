define(['Reports/Main/Ctrl/Base'], function(Reports_Main_Ctrl_Bare) {
  class Reports_Main_Ctrl_BackToAgent extends Reports_Main_Ctrl_Bare {
    static initClass() {
      this.CTRL_ID   = 'Reports_Main_Ctrl_BackToAgent';
      this.DEPS      = ['$location'];
    }

    init() {
      // Redirect back to agent
      // Unless this is in an iframe, in which case simply viewing
      // this route will cause the parent to close the iframe and
      // make the agent interface visible again
      if (!window.parent || !window.parent.DP_FRAME_OVERLAYS || !window.parent.DP_FRAME_OVERLAYS.reports) {
        return window.location.href = `${window.DP_BASE_URL}agent/`;
      }
      return window.parent.DP_FRAME_OVERLAYS.reports.close();
    }
  }
  Reports_Main_Ctrl_BackToAgent.initClass();

  return Reports_Main_Ctrl_BackToAgent.EXPORT_CTRL();
});
