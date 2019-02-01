define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_Settings_Ctrl_RegSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Settings_Ctrl_RegSettings';
      this.CTRL_AS   = 'Settings';
      this.DEPS      = [];
    }

    init() {
      return this.settings = null;
    }

    initialLoad() {
      const data_promise = this.Api.sendGet('/registration_settings', {rate_limit_context: 'user'}).then( res => {
        this.$scope.settings = res.data.registration_settings;
        this.settings = angular.copy(this.$scope.settings);
        return this.$scope.rate_limit_settings = res.data.rate_limit_settings;
      });

      return this.$q.all([data_promise]);
    }

    isDirtyState() {
      if (!this.settings) { return false; }
      if (!angular.equals(this.settings, this.$scope.settings)) {
        return true;
      } else {
        return false;
      }
    }

    save() {
      let promise;
      const postData = {
        registration_settings: this.$scope.settings,
        rate_limit_settings: this.$scope.rate_limit_settings,
        rate_limit_context: 'user'
      };

      if ((postData.registration_settings.reg_enabled === "1") || (postData.registration_settings.reg_enabled === 1) || (postData.registration_settings.reg_enabled === true)) {
        postData.registration_settings.reg_enabled = true;
      } else {
        postData.registration_settings.reg_enabled = false;
      }

      this.startSpinner('saving');
      return promise = this.Api.sendPostJson('/registration_settings', postData).success( () => {
        this.settings = angular.copy(this.$scope.settings);

        return this.stopSpinner('saving').then(() => {
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        });
      }).error( (info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_Settings_Ctrl_RegSettings.initClass();

  return Admin_Settings_Ctrl_RegSettings.EXPORT_CTRL();
});