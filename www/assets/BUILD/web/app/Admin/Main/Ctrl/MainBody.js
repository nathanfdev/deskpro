define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Main_Ctrl_MainBody extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_MainBody';
      this.DEPS      = [];
    }
  }
  Admin_Main_Ctrl_MainBody.initClass();

  return Admin_Main_Ctrl_MainBody.EXPORT_CTRL();
});
