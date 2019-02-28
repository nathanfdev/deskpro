define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_Settings_Ctrl_PortalSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Settings_Ctrl_PortalSettings';
      this.CTRL_AS   = 'Settings';
      this.DEPS      = [];
    }

    init() {
      return this.settings = null;
    }

    initialLoad() {
      const data_promise = this.Api.sendDataGet({
        settings: '/portal_settings'
      }).then((res) => {
        this.$scope.settings = res.data.settings.portal_settings;
        this.$scope.show_ratings_opt = false;
        if (this.$scope.settings.show_ratings > 0) {
          this.$scope.show_ratings_opt = true;
        } else {
          this.$scope.settings.show_ratings = 1;
        }

        return this.settings = angular.copy(this.$scope.settings);
      });

      return this.$q.all([data_promise]);
    }

    isDirtyState() {
      if (!this.settings) { return false; }
      if (!angular.equals(this.settings, this.$scope.settings)) {
        return true;
      }
      return false;
    }

    save() {
      let promise;
      if (!this.$scope.show_ratings_opt) {
        this.$scope.settings.show_ratings = 0;
      }

      const postData = {
        portal_settings: this.$scope.settings
      };

      this.startSpinner('saving');
      return promise = this.Api.sendPostJson('/portal_settings', postData).success(() => {
        this.settings = angular.copy(this.$scope.settings);

        return this.stopSpinner('saving').then(() => this.Growl.success(this.getRegisteredMessage('saved_settings')));
      }).error((info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_Settings_Ctrl_PortalSettings.initClass();

  return Admin_Settings_Ctrl_PortalSettings.EXPORT_CTRL();
});
