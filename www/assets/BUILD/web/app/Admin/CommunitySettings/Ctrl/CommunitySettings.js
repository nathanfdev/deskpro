define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_CommunitySettings_Ctrl_CommunitySettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_CommunitySettings_Ctrl_CommunitySettings';
      this.CTRL_AS = 'Ctrl';
      this.DEPS    = [];
    }

    init() {
      return this.$scope.brand_id = this.$stateParams.brandId;
    }

    initialLoad() {
      return this.Api2.sendGet(`/settings/brands/${this.$scope.brand_id}/portal/community`).then(res => this.$scope.settings = res.data.data);
    }

    save() {
      this.startSpinner('saving');
      return this.Api2.sendPostJson(`/settings/brands/${this.$scope.brand_id}/portal/community`, this.$scope.settings).then(() => {
        this.Growl.success('Settings saved');
        return this.stopSpinner('saving');
      });
    }
  }
  Admin_CommunitySettings_Ctrl_CommunitySettings.initClass();


  return Admin_CommunitySettings_Ctrl_CommunitySettings.EXPORT_CTRL();
});
