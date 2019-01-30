/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Reports/Main/Ctrl/Base'
], function(
  ReportsBaseCtrl
) {
  class Reports_Main_Ctrl_MainPage extends ReportsBaseCtrl {
    static initClass() {
      this.CTRL_ID   = 'Reports_Main_Ctrl_MainPage';
      this.DEPS      = ['$rootScope', 'AppState'];
    }

    init() {
    }
  }
  Reports_Main_Ctrl_MainPage.initClass();

  return Reports_Main_Ctrl_MainPage.EXPORT_CTRL();
});