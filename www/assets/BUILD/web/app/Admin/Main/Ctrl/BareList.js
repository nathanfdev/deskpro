define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Main_Ctrl_BareList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_BareList';
    }
  }
  Admin_Main_Ctrl_BareList.initClass();

  return Admin_Main_Ctrl_BareList.EXPORT_CTRL();
});
