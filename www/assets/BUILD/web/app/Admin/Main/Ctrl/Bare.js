define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Main_Ctrl_Bare extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_Bare';
    }
  }
  Admin_Main_Ctrl_Bare.initClass();

  return Admin_Main_Ctrl_Bare.EXPORT_CTRL();
});