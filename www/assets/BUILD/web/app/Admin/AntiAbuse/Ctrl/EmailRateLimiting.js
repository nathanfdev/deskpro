define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  const _url = '/email_accounts/settings';
  class Admin_AntiAbuse_Ctrl_EmailRateLimiting extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_AntiAbuse_Ctrl_EmailRateLimiting';
      this.CTRL_AS = 'EmailRateLimiting';
    }

    init() {
      return this.$scope.settings = null;
    }

    initialLoad() {
      return this.Api.sendGet(_url).then(res => this.$scope.settings = res.data.email_settings);
    }

    save() {
      const postData = {
        settings: this.$scope.settings
      };

      this.startSpinner('saving');
      return this.Api.sendPutJson(_url, postData).success(() => {
        this.stopSpinner('saving');
        return this.Growl.success(this.getRegisteredMessage('saved_settings'));
      }).error((info) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_AntiAbuse_Ctrl_EmailRateLimiting.initClass();
  return Admin_AntiAbuse_Ctrl_EmailRateLimiting.EXPORT_CTRL();
});
