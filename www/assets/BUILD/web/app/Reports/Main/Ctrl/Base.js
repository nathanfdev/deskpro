// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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