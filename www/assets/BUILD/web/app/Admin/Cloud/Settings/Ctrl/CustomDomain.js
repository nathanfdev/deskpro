/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Cloud_Settings_Ctrl_CustomDomain extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Cloud_Settings_Ctrl_CustomDomain';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS = ['$timeout'];
    }

    initialLoad() {
      return this.Api.sendGet('/settings/cloud/url-settings').then( res => {
        return this.$scope.form = res.data.settings;
      });
    }

    setupCustomDomain(domain) {
      const d = this.$q.defer();

      this.Api.sendPostJson('/settings/cloud/setup-custom-domain', { domain }).then( res => {
        console.log(res);

        if (res.data.error) {
          this.$scope.ma_pending_message = null;
          this.$scope.ma_error_message = res.data.message;
          return d.reject();
        } else if (!res.data.error && !res.data.domain_id) {
          this.$scope.ma_error_message = null;
          this.$scope.ma_pending_message = res.data.message;
          return this.$timeout(() => {
            return this.setupCustomDomain(domain).then(() => {
              return d.resolve();
            }
            , () => {
              return d.reject();
            });
          }
          , 3000);
        } else {
          this.$scope.ma_pending_message = null;
          this.$scope.ma_pending_message = 'Your custom domain has been configured. It might take a few minutes for your domain to become fully functional.';
          return d.resolve();
        }
      }
      , () => {
        this.$scope.form_error = 'server_error';
        return d.reject();
      });

      return d.promise;
    }

    save() {
      this.$scope.form_error = null;
      this.startSpinner('saving');
      const postData = {
        settings: this.$scope.form
      };

      const d = this.$q.defer();

      this.$scope.ma_pending_message = null;
      this.$scope.ma_error_message = null;

      if (postData.settings.domain_choice === 'custom') {
        this.$scope.ma_pending_message = 'Checking your custom domain';
        this.setupCustomDomain(postData.settings.cloud_custom_domain).then(() => {
          return d.resolve();
        }
        , () => {
          return d.reject();
        });
      } else {
        d.resolve();
      }

      return d.promise.then(() => {
        return this.Api.sendPostJson('/settings/cloud/url-settings', postData).then(() => {
          return this.stopSpinner('saving').then(() => {
            return this.Growl.success(this.getRegisteredMessage('saved_settings'));
          });
        }
        , res => {
          this.stopSpinner('saving', true);
          if ((res.data != null ? res.data.error_code : undefined) != null) {
            return this.$scope.form_error = res.data.error_code;
          } else {
            return this.$scope.form_error = 'server_error';
          }
        });
      }
      , () => {
        return this.stopSpinner('saving', true);
      });
    }
  }
  Admin_Cloud_Settings_Ctrl_CustomDomain.initClass();

  return Admin_Cloud_Settings_Ctrl_CustomDomain.EXPORT_CTRL();
});