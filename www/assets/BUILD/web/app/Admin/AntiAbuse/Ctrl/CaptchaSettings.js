define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  const _url = '/settings/anti_abuse/captcha';
  class Admin_AntiAbuse_Ctrl_CaptchaSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_AntiAbuse_Ctrl_CaptchaSettings';
      this.CTRL_AS = 'CaptchaSettings';
    }

    init() {
      this.$scope.settings = null;
      return this.$scope.general_settings = null;
    }

    initialLoad() {
      const captchaPromise = this.Api2.sendGet(_url).then(res => this.$scope.settings = res.data.data);
      const generalPromise = this.Api.sendGet('/general_settings').then(res => this.$scope.general_settings = res.data.general_settings);

      return this.$q.all([captchaPromise, generalPromise]);
    }

    save() {
      const captchaPromise = this.Api2.sendPutJson(_url, this.$scope.settings);
      const generalPromise = this.Api.sendPostJson('/general_settings', {
        general_settings: this.$scope.general_settings
      });
      this.startSpinner('saving');
      return this.$q.all([captchaPromise, generalPromise]).then(() => {
        this.stopSpinner('saving');
        return this.Growl.success(this.getRegisteredMessage('saved_settings'));
      }
      , (info) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_AntiAbuse_Ctrl_CaptchaSettings.initClass();
  return Admin_AntiAbuse_Ctrl_CaptchaSettings.EXPORT_CTRL();
});
