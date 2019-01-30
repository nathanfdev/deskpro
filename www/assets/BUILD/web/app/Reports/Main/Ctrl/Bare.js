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
  class Reports_Main_Ctrl_Bare extends DeskPROBaseCtrl {
    static initClass() {
      this.CTRL_ID = 'Reports_Main_Ctrl_Bare';
    }
  }
  Reports_Main_Ctrl_Bare.initClass();

  return Reports_Main_Ctrl_Bare.EXPORT_CTRL();
});