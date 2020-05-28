define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_CustomFields_Community_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_CustomFields_Community_Ctrl_List';
      this.DEPS = [];
      this.CTRL_AS = 'ListCtrl';
    }

    init() {
      this.$scope.brand_id = this.$stateParams.brandId;
      this.$scope.fields = [];
    }

    initialLoad() {
      return this.DataService.get('CommunityFields').loadList().then((list) => {
        this.$scope.fields = list;
      });
    }
  }
  Admin_CustomFields_Community_Ctrl_List.initClass();

  return Admin_CustomFields_Community_Ctrl_List.EXPORT_CTRL();
});
