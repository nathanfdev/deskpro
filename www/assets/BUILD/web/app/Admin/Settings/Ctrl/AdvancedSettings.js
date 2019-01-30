// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_Settings_Ctrl_AdvancedSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Settings_Ctrl_AdvancedSettings';
      this.CTRL_AS   = 'Settings';
      this.DEPS      = [];
    }

    init() {
      return this.settings = null;
    }

    initialLoad() {
      const data_promise = this.Api.sendDataGet({
        'settings': '/all_settings_raw'
      }).then( res => {
        return this.$scope.settings = res.data.settings.all_settings;
      });

      return this.$q.all([data_promise]);
    }

    save() {
      let promise;
      const postData = {
        all_settings: {}
      };

      for (let setting of Array.from(this.$scope.settings)) {
        postData.all_settings[setting.name] = setting.value;
      }

      this.startSpinner('saving');
      return promise = this.Api.sendPostJson('/all_settings_raw', postData).success( () => {
        return this.stopSpinner('saving').then(() => {
          return this.Growl.success(this.getRegisteredMessage('saved_settings'));
        });
      }).error( (info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_Settings_Ctrl_AdvancedSettings.initClass();

  return Admin_Settings_Ctrl_AdvancedSettings.EXPORT_CTRL();
});