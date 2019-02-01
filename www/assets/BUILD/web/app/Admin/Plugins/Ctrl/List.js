define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Plugins_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Plugins_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      return this.packages = [];
    }

    initialLoad() {
      const promise = this.Api.sendGet('/plugins/packages').then(result => this.packages = result.data.packages);
      return promise;
    }
  }
  Admin_Plugins_Ctrl_List.initClass();

  return Admin_Plugins_Ctrl_List.EXPORT_CTRL();
});
