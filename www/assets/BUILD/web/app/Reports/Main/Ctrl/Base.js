define([
  'DeskPRO/Main/Ctrl/Base'
], function(
  DeskPROBaseCtrl
) {
  class Reports_Ctrl_Base extends DeskPROBaseCtrl {
    static initClass() {
      this.CTRL_AS   = null;
      this.CTRL_ID   = 'Reports_Main_Ctrl_Base';
      this.DEPS      = [];
    }

    /**
    * Get the URL to the template
    *
    * @return {String}
    */
    getTemplatePath(path) {
      return DP_BASE_REPORTS_URL + '/load-view/' + path;
    }
  }
  Reports_Ctrl_Base.initClass();
  return Reports_Ctrl_Base;
});