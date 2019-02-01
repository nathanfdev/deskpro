define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_GuidesSettings_Ctrl_GuidesSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_GuidesSettings_Ctrl_GuidesSettings';
      this.CTRL_AS = 'Ctrl';
      this.DEPS    = [];
    }

    init() {
      return this.$scope.brand_id = this.$stateParams.brandId;
    }

    initialLoad() {
      return this.Api2.sendGet(`/settings/brands/${this.$scope.brand_id}/portal/guides`).then( res => {
        return this.$scope.settings = res.data.data;
      });
    }

    save() {
      this.startSpinner('saving');
      return this.Api2.sendPostJson(`/settings/brands/${this.$scope.brand_id}/portal/guides`, this.$scope.settings).then( () => {
        this.Growl.success("Settings saved");
        return this.stopSpinner('saving');
      });
    }
  }
  Admin_GuidesSettings_Ctrl_GuidesSettings.initClass();


  return Admin_GuidesSettings_Ctrl_GuidesSettings.EXPORT_CTRL();
});
