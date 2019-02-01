define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_UserGroups_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_UserGroups_Ctrl_List';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS = [];
    }

    init() {
      return this.ugData = this.DataService.get('UserGroups');
    }

    initialLoad() {
      const promise = this.ugData.loadList().then( list => {
        return this.list = list;
      });

      return promise;
    }
  }
  Admin_UserGroups_Ctrl_List.initClass();

  return Admin_UserGroups_Ctrl_List.EXPORT_CTRL();
});