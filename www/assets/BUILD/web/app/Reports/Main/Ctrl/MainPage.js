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