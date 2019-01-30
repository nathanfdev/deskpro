/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Portal_Ctrl_Setup extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Portal_Ctrl_Setup';
      this.CTRL_AS = 'Ctrl';
      this.DEPS = ['$timeout'];
    }

    init() {
      this.settings = {
        deskpro_url: '',
        deskpro_name: '',
        deskpro_domain: '',
        domain_choice: 'default',
        orig_deskpro_url: null
      };
      this.portalSettings = this.DataService.get('PortalGeneralSettings');

      this.$scope.brand_id = this.$stateParams.brandId;
      this.$scope.brand = false;

      this.Api2.sendGet('brands/default').then(res => {
        return this.$scope.default_brand = res.data.data;
      });

      this.$scope.$on('icon.selected', (e, path) => this.selectIcon(path));

      this.$scope.$watch('brand_id', () => {
        this.portalSettings.setBrandId(this.$scope.brand_id);
        return this.portalSettings.getSettings().then(s => { return this.settings = s; });
      });

      const tmpUpdate = () => this.portalSettings.updateSettingsTemporary(this.settings);
      for (let i of ['apps_feedback', 'apps_kb', 'apps_news', 'apps_downloads', 'iface_portal', 'iface_widget']) {
        this.$scope.$watch(`Ctrl.settings.${i}`, tmpUpdate);
      }

      if (window.DP_IS_CLOUD) {
        const updateFn = () => {
          if (!this.settings.deskpro_domain || (this.settings.deskpro_domain === '')) {
            this.settings.deskpro_url = '';
            return;
          }

          if (this.settings.domain_choice === 'default') {
            return this.settings.deskpro_url = `https://${this.settings.deskpro_domain}.deskpro.com/`;
          } else {
            return this.settings.deskpro_url = `https://${this.settings.deskpro_domain}/`;
          }
        };

        return this.$scope.$watch('Ctrl.settings.deskpro_domain + Ctrl.settings.domain_choice', updateFn);
      }
    }

    setPortalMode(v) {
      if (v === "publish") {
        this.settings.portal_mode = "publish";
        this.settings.apps_downloads = true;
        this.settings.apps_feedback = true;
        this.settings.apps_guides = true;
        this.settings.apps_kb = true;
        this.settings.apps_news = true;
      } else {
        this.settings.portal_mode = "tickets";
        this.settings.apps_downloads = false;
        this.settings.apps_feedback = false;
        this.settings.apps_guides = false;
        this.settings.apps_kb = false;
        this.settings.apps_news = false;
      }

      return this.portalSettings.updateSettingsTemporary(this.settings);
    }

    initialLoad() {
      const promises = [];
      if (this.$scope.brand_id !== 'new') {
        this.portalSettings.setBrandId(this.$scope.brand_id);

        const settingPromise = this.portalSettings.getSettings();

        settingPromise
          .then(res => { return this.settings = res; });
        promises.push(settingPromise);

        const brandPromise = this.Api2.sendGet(`/brands/${this.$scope.brand_id}`);
        brandPromise.then(res => {
          return this.$scope.brand = res.data.data;
        });

        promises.push(brandPromise);
      }

      return this.$q.all(promises);
    }

    cloudSetupHost() {

      this.$scope.ma_error_message = null;
      this.$scope.ma_pending_message = null;

      const d = this.$q.defer();

      if (!window.DP_IS_CLOUD) {
        d.resolve();
        return d.promise;
      }

      const input = this.settings.deskpro_url;

      const parser = document.createElement('a');
      parser.href = input;
      const domain = parser.hostname;

      if (!domain) {
        this.Growl.error("You must specify a name and a url");
        $('#helpdesk_name').focus();
        r.reject();
        return;
      }

      this.settings.deskpro_url = `https://${domain}/`;

      if (this.settings.orig_deskpro_url === this.settings.deskpro_url) {
        d.resolve();
        return d.promise;
      }

      this.$scope.ma_pending_message = 'Checking your custom domain';

      this.setupCustomDomain(domain).then(() => {
        return d.resolve();
      }
      , () => {
        return d.reject();
      });

      return d.promise;
    }

    setupCustomDomain(domain) {
      const d = this.$q.defer();

      this.Api.sendPostJson('/settings/cloud/setup-custom-domain?allowProvider', { domain }).then( res => {
        console.log(res);

        if (res.data.error) {
          this.$scope.ma_pending_message = null;
          this.$scope.form_error = 'ma_error_message';
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
          this.$scope.ma_error_message = null;
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

    saveSettings(skipCloudCheck) {
      let checkP, d;
      if (!this.settings.deskpro_name) {
        this.Growl.error("You must specify a name");
        $('#helpdesk_name').focus();
        return false;
      }

      if (this.settings.deskpro_url && !this.settings.deskpro_url.match(/^https?:\/\//i)) {
        this.settings.deskpro_url = `https://${this.settings.deskpro_url}`;
      }

      this.startSpinner();

      if (window.DP_IS_CLOUD) {
        if (skipCloudCheck) {
          d = this.$q.defer();
          d.resolve();
          checkP = d.promise;
        } else {
          checkP = this.cloudSetupHost();
        }
      } else {
        d = this.$q.defer();
        d.resolve();
        checkP = d.promise;
      }

      return checkP.then(() => {
        return this.portalSettings.updateSettings(this.settings).then(s => {
          this.settings = s;
          this.originalUrl === this.settings.deskpro_url;
          this.stopSpinner();
          return this.$scope.$emit('dp-update-brands');
        }
        , () => {
          return this.stopSpinner();
        });
      }
      , () => {
        return this.stopSpinner();
      });
    }

    createBrand() {
      let checkP;
      if (!this.settings.brand_name) {
        if (this.settings.deskpro_name) {
          this.settings.brand_name = this.settings.deskpro_name;
        } else if (this.settings.deskpro_url) {
          this.settings.brand_name = this.settings.deskpro_url.replace(/^https?:\/\//i, '').replace(/\/+$/, '');
        }
      }

      if (!this.settings.deskpro_name) {
        if (this.settings.brand_name) {
          this.settings.deskpro_name = this.settings.brand_name;
        } else if (this.settings.deskpro_url) {
          this.settings.deskpro_name = this.settings.deskpro_url.replace(/^https?:\/\//i, '').replace(/\/+$/, '');
        }
      }

      if (!this.settings.deskpro_name) {
        this.Growl.error("You must specify a name");
        $('#helpdesk_name').focus();
        return false;
      }

      if (this.settings.deskpro_url && !this.settings.deskpro_url.match(/^https?:\/\//i)) {
        this.settings.deskpro_url = `https://${this.settings.deskpro_url}`;
      }

      const brand = {
        name: this.settings.deskpro_name,
        slug: this.settings.brand_slug
      };

      if (this.settings.deskpro_url) {
        brand.url = this.settings.deskpro_url;
      }

      this.startSpinner();

      if (window.DP_IS_CLOUD) {
        checkP = this.cloudSetupHost();
      } else {
        checkP = this.Api2.sendPostJson('/brands/check_url', {url: this.settings.deskpro_url});
      }

      return checkP.then( res => {
        if (!res || res.data.data.free) {
          return this.Api2.sendPostJson('brands', brand).then(res => {
            this.Growl.success("Brand created");
            this.$scope.brand_id = res.data.data.id;
            this.portalSettings.setBrandId(res.data.data.id);
            this.brandId = res.data.data.id;
            return this.saveSettings(true).then(() => {
              this.stopSpinner();
              return this.$state.go('portal.setup', {brandId: this.brandId});
            });
          }
          , res => {
            this.Growl.error(res.data.message);
            return this.stopSpinner();
          });
        } else if (res.data.data.reason) {
          this.Growl.error(res.data.data.reason);
          $('#helpdesk_url').focus();
          return this.stopSpinner();
        } else {
          this.Growl.error("Each brand need to have a different url");
          $('#helpdesk_url').focus();
          return this.stopSpinner();
        }
      }
      , () => {
        this.stopSpinner();
        return this.Growl.error("We can't check this url. Try another one or contact your system administrator.");
      });
    }

    deleteBrand() {
      if (confirm("Are you sure you want to delete this brand? Deleting the brand will re-assign tickets and chat to the default brand. Theme personalization and templates will be lost.")) {
        return this.Api2.sendDelete(`brands/${this.$scope.brand_id}`).then(() => {
          this.Growl.success("Brand deleted");
          this.$state.go('portal.setup', {brandId: this.$scope.default_brand.id});
          return this.$scope.$emit('dp-update-brands');
        });
      }
    }
  }
  Admin_Portal_Ctrl_Setup.initClass();

  return Admin_Portal_Ctrl_Setup.EXPORT_CTRL();
});
