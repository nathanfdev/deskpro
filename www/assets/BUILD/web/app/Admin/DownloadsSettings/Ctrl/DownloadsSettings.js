/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_DownloadsSettings_Ctrl_DownloadsSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_DownloadsSettings_Ctrl_DownloadsSettings';
      this.CTRL_AS = 'Ctrl';
      this.DEPS    = [];
    }

    init() {
      return this.$scope.brand_id = this.$stateParams.brandId;
    }

    initialLoad() {
      return this.Api2.sendGet(`/settings/brands/${this.$scope.brand_id}/portal/downloads`).then( res => {
        return this.$scope.settings = res.data.data;
      });
    }

    save() {
      this.startSpinner('saving');
      return this.Api2.sendPostJson(`/settings/brands/${this.$scope.brand_id}/portal/downloads`, this.$scope.settings).then( () => {
        this.Growl.success("Settings saved");
        return this.stopSpinner('saving');
      });
    }
  }
  Admin_DownloadsSettings_Ctrl_DownloadsSettings.initClass();


  return Admin_DownloadsSettings_Ctrl_DownloadsSettings.EXPORT_CTRL();
});