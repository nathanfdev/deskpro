define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_FeedbackSettings_Ctrl_FeedbackSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_FeedbackSettings_Ctrl_FeedbackSettings';
      this.CTRL_AS = 'Ctrl';
      this.DEPS    = [];
    }

    init() {
      return this.$scope.brand_id = this.$stateParams.brandId;
    }

    initialLoad() {
      return this.Api2.sendGet(`/settings/brands/${this.$scope.brand_id}/portal/feedback`).then(res => this.$scope.settings = res.data.data);
    }

    save() {
      this.startSpinner('saving');
      return this.Api2.sendPostJson(`/settings/brands/${this.$scope.brand_id}/portal/feedback`, this.$scope.settings).then(() => {
        this.Growl.success('Settings saved');
        return this.stopSpinner('saving');
      });
    }
  }
  Admin_FeedbackSettings_Ctrl_FeedbackSettings.initClass();


  return Admin_FeedbackSettings_Ctrl_FeedbackSettings.EXPORT_CTRL();
});
