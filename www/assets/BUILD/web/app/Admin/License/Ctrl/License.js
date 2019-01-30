/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_License_Ctrl_License extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_License_Ctrl_License';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$window', 'DpLicense'];
    }

    init() {
      this.license          = null;
      this.ma_token         = null;
      this.ma_login_url     = null;
      this.lic_set_callback = null;
      this.$scope.form = { lic_code: '' };

      this.$scope.$watch('form.lic_code', lic_code => {
        lic_code = lic_code || '';
        lic_code = lic_code.replace(/\s/g, '');
        lic_code = (lic_code.match(/(.{1,50})/g) || [lic_code]).join("\n");
        return this.$scope.form.lic_code = lic_code;
      });
      const old_title = window.document.title;
      window.document.title = 'DeskPRO Billing Interface';
      return this.$scope.$on('$destroy', () => window.document.title = old_title);
    }

    reloadLicData() {
      const data_promise = this.Api.sendDataGet({
        'lic_info': '/dp_license'
      }).then( res => {
        this.license          = res.data.lic_info.license;
        this.license.countAgents = res.data.lic_info.limits.count_agents;
        this.ma_token         = res.data.lic_info.ma_token;
        this.ma_login_url     = res.data.lic_info.ma_login_url;
        this.lic_set_callback = res.data.lic_info.lic_set_callback;

        this.$scope.form.lic_code = this.license.licenseCode;

        this.$scope.refreshing_lic = true;
        this.DpLicense.getNewLicenseKey().then( res => {
          const cmp1 = res.license_code.replace(/[^a-zA-Z0-9]/g, '');
          const cmp2 = this.license.licenseCode.replace(/[^a-zA-Z0-9]/g, '');

          if (cmp1 === cmp2) {
            return this.$scope.refreshing_lic = false;
          } else {
            return this.DpLicense.setNewLicenseCode(res.license_code).then( () => {
              this.$scope.refreshing_lic = false;
              let lic_code = res.license_code;
              lic_code = lic_code.replace(/\s/g, '');
              lic_code = (lic_code.match(/(.{1,50})/g) || [lic_code]).join("\n");
              this.$scope.form.lic_code = lic_code;
              return this.license.licenseCode = lic_code;
            }
            , () => {
              return this.$scope.refreshing_lic = false;
            });
          }
        }
        , () => {
          return this.$scope.refreshing_lic = false;
        });

        if (res.data.lic_info.custom_billing_frame) {
          this.$scope.custom_billing_frame = res.data.lic_info.custom_billing_frame;
          this.$scope.iframe_loading = false;
          return this.$scope.iframe_code    = `<iframe src="${this.$scope.custom_billing_frame}" frameborder="0"></iframe>`;
        }
      });

      return data_promise;
    }

    initialLoad() {
      return this.reloadLicData();
    }

    downloadKeyfile() {
      return this.$window.location = this.Api.formatUrl('dp_license/keyfile.txt') + '?API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN;
    }

    goToMembersArea() {
      this.$window.location = 'https://www.deskpro.com/members/';
    }

    saveLicenseCode() {
      const postData = {
        license_code: this.$scope.form.lic_code
      };

      this.$scope.lic_error_code = false;
      this.$scope.show_lic_error = false;
      this.startSpinner('saving');
      return this.Api.sendPost("dp_license", postData).success(() => {
        return this.DpLicense.getLicInfo(true).then(
          this.reloadLicData().then(() => {
            return this.stopSpinner('saving').then(() => {
              return this.Growl.success(this.getRegisteredMessage('saved_lic'));
            });
          })
        );
      }).error( data => {
        this.stopSpinner('saving', true);
        this.$scope.lic_error_code = false;
        if (data && data.error_code) {
          this.$scope.lic_error_code = data.error_code;
        }

        return this.$scope.show_lic_error = true;
      });
    }

    save() {
    }

    openUpgradeLicense() {
      return this.DpLicense.openUpgradeLicense('add_agents').then(() => this.$state.go('license_go') );
    }

    openRenewLicense() {
      return this.DpLicense.openRenewLicense().then(() => this.$state.go('license_go') );
    }
  }
  Admin_License_Ctrl_License.initClass();

  return Admin_License_Ctrl_License.EXPORT_CTRL();
});