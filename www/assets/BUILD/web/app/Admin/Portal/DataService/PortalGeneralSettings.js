define(['DeskPRO/Util/Util'], function(Util) {
  class Admin_Portal_Service_PortalGeneralSettings {
    static initClass() {
      this.$inject = ['Api2', '$q', 'Growl'];
    }

    constructor(Api2, $q) {
      this.Api2 = Api2;
      this.$q = $q;
    }

    init() {
      // simple counter that other controllers
      // watch to know when the settings object
      // has changed
      this.version = 1;
      this.settingPromise = [];
      this.settings = null;
      return this.brandId = null;
    }

    setBrandId(brandId) {
      return this.brandId = brandId;
    }

    /*
     * Unsets loaded settings cache. Next time it is fetched, a HTTP call will be made.
     */
    reset() {
      return this.settings = null;
    }

    /*
     * Load settings from the database.
     * If settings were already loaded, those cached values are returned.
     *
     * Note: A COPY of the settings object is returned.
     * You should watch @version to know when you should re-fetch settings.
     *
     * @return {promise}
     */
    getSettings() {
      const d = this.$q.defer();

      if (this.settingPromise[this.brandId]) {
        this.settingPromise[this.brandId].then(r => d.resolve(Util.clone(r, true))
        , (r, s) => d.reject(r, s));
      } else if (this.settings && (this.settings.brand === this.brandId)) {
        d.resolve(Util.clone(this.settings, true));
      } else {
        this._loadSettings().then((r) => {
          if (r.data) {
            return d.resolve(Util.clone(r.data.data, true));
          }
        }
        , (r, s) => d.reject(r, s));
      }

      return d.promise;
    }

    /*
     * Reload settings from the database. Any cached values will be discarded.
     *
     * @return {promise}
     */
    _loadSettings() {
      let p;
      const d = this.$q.defer();
      this.settingPromise[this.brandId] = d.promise;

      const me = this;

      if (this.brandId && (this.brandId !== 'new')) {
        p = this.Api2.sendGet(`/settings/brands/${this.brandId}/portal/general`).success((res) => {
          if (me.brandId === 'new') {
            res.data.deskpro_url = '';
            res.data.deskpro_name = '';
          }

          let domain = false;
          try {
            const parser = document.createElement('a');
            parser.href = res.data.deskpro_url;
            domain = parser.hostname || false;
          } catch (error) {}

          if (!domain) {
            domain = res.data.deskpro_url.replace(/^https?:\/\//i, '').replace(/\/+/, '');
          }

          res.data.deskpro_domain = domain;
          res.data.domain_choice  = !domain || (domain.indexOf('.deskpro.com') !== -1) ? 'default' : 'custom';

          if (res.data.domain_choice === 'default') {
            res.data.deskpro_domain = res.data.deskpro_domain.replace(/\.deskpro\.com$/, '');
          }

          me.settings = res.data;
          me.settings.orig_deskpro_url = me.settings.deskpro_url;
          d.resolve(me.settings);
          return me.settingPromise[me.brandId] = null;
        }).error((data, status) => {
          d.reject(data, status);
          return me.settingPromise[me.brandId] = null;
        });
      } else {
        p = this.$q.when({
          deskpro_url:      '',
          deskpro_name:     '',
          deskpro_domain:   '',
          domain_choice:    'default',
          orig_deskpro_url: null
        });
      }

      return p;
    }

    /*
     * Updates settings with those provided, then will reload
     * settings to make current set up to date.
     */
    updateSettings(settings) {
      const d = this.$q.defer();

      const data = angular.copy(settings);

      const me = this;
      // Virtual value we don't want to save it
      delete data.brand;
      delete data.portal_mode;
      delete data.enable_brand_logo;

      this.Api2.sendPostJson(`/settings/brands/${this.brandId}/portal/general`, data).then(res =>
        me._loadSettings().then(() => d.resolve(res.data.data)
        , () => d.resolve(res.data.data))

      , (res, status) => {
        d.reject(res, status);
        if (res.data != null ? res.data.message : undefined) { return this.Api2.Growl.error(res.data.message); }
      });

      return d.promise;
    }

    updateSettingsTemporary(settings) {
      const newSettings = Util.merge(this.settings, Util.clone(settings, true));

      this.version += 1;
      return this.settings = newSettings;
    }
  }
  Admin_Portal_Service_PortalGeneralSettings.initClass();
  return Admin_Portal_Service_PortalGeneralSettings;
});
