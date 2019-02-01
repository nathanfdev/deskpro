define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  const _url = '/settings/anti_abuse/portal';
  class Admin_AntiAbuse_Ctrl_PortalRateLimiting extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_AntiAbuse_Ctrl_PortalRateLimiting';
      this.CTRL_AS = 'PortalRateLimiting';
    }

    init() {
      this.$scope.settings = null;
      this.$scope.feedbackSettings = null;
      this.$scope.usersourceSettings = null;
      return this.$scope.general_settings = null;
    }

    initialLoad() {
      const promise = this.Api2.sendGet(_url).then(res => {
        return this.$scope.settings = res.data.data;
      });
      const feedbackPromise = this.Api.sendGet('/settings/portal/feedback').then(res => {
        return this.$scope.feedbackSettings = res.data.settings;
      });
      const usersourcePromise = this.Api2.sendGet('/settings/user_source').then(res => {
        return this.$scope.usersourceSettings = res.data.data;
      });
      const generalPromise = this.Api.sendGet('/general_settings').then(res => {
        return this.$scope.general_settings = res.data.general_settings;
      });

      return this.$q.all([promise, feedbackPromise, usersourcePromise, generalPromise]);
    }

    save() {
      const promise = this.Api2.sendPutJson(_url, this.$scope.settings);
      const generalPromise = this.Api.sendPostJson('/general_settings', {
        general_settings: this.$scope.general_settings
      });

      this.startSpinner('saving');

      return this.$q.all([promise, generalPromise]).then( () => {
        this.stopSpinner('saving');
        return this.Growl.success(this.getRegisteredMessage('saved_settings'));
      }
      , info => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_AntiAbuse_Ctrl_PortalRateLimiting.initClass();
  return Admin_AntiAbuse_Ctrl_PortalRateLimiting.EXPORT_CTRL();
});
