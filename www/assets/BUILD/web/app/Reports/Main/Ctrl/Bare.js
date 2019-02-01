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