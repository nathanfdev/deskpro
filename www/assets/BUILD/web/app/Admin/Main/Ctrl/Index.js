define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Main_Ctrl_Index extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_Index';
    }

    init() {}
  }
  Admin_Main_Ctrl_Index.initClass();

  return Admin_Main_Ctrl_Index.EXPORT_CTRL();
});