/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  let Admin_License_Service_DpLicense;
  return (Admin_License_Service_DpLicense = class Admin_License_Service_DpLicense {
    constructor(Api, $modal, $http, $q) {
      this.Api = Api;
      this.$modal = $modal;
      this.$http = $http;
      this.$q = $q;
      this.shortAuthCode = null;
    }

    getLicInfo(reload) {
      if (this.licGetting && !reload) { return this.licGetting.promise; }
      this.licGetting = this.$q.defer();

      this.Api.sendGet('/dp_license').success( data => {
        this.info = data;
        this.licInfo = data.license;
        this.licInfo.licenseCode = this.licInfo.licenseCode.replace(/\s/g, '');

        return this.licGetting.resolve(this.info);
      }
      , () => {
        this.licGetting.reject();
        return this.licGetting = null;
      });

      return this.licGetting.promise;
    }

    getLicServerParams() {
      return { 'license_id': this.licInfo.licenseId, 'license_code': this.licInfo.licenseCode, 'callback': 'JSON_CALLBACK', 'email': window.DP_PERSON_EMAIL };
    }

    getPlanUpgradeInfo(num_agents) {
      const d = this.$q.defer();

      this.getLicInfo().then(() => {
        const params = this.getLicServerParams();
        params.num_agents = num_agents || 0;

        return this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/plan-info', {
          params,
          timeout: 25000,
          cache: false
        }).then(x => d.resolve(x.data, x)
        , x => d.reject(x.data, x));
      }
      , x => d.reject(x));

      return d.promise;
    }

    getRenewInfo(num_years) {
      const d = this.$q.defer();

      this.getLicInfo().then(() => {
        const params = this.getLicServerParams();
        params.num_years = num_years || 0;

        return this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/renew-info', {
          params,
          timeout: 25000,
          cache: false
        }).then(x => d.resolve(x.data, x)
        , x => d.reject(x.data, x));
      }
      , x => d.reject(x));

      return d.promise;
    }

    getNewLicenseKey() {
      const d = this.$q.defer();
      this.getLicInfo().then(() => {
        return this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/renew-key', {
          params: this.getLicServerParams(),
          timeout: 25000,
          cache: false
        }).then(function(x) {
          if ((x.data != null ? x.data.error_code : undefined)) {
            return d.reject(x.data, x);
          } else {
            return d.resolve(x.data, x);
          }
        }
        , x => d.reject(x.data, x));
      }
      , x => d.reject(x));

      return d.promise;
    }

    setNewLicenseCode(lic_code) {
      const postData = {
        license_code: lic_code
      };

      const d = this.$q.defer();

      this.Api.sendPost("dp_license", postData).success(() => {
        this.getLicInfo(true);
        return d.resolve({success: true, lic_code});
      }).error( data => {
        return d.reject({success: false, lic_code, error_code: (data != null ? data.error_code : undefined)});
      });

      return d.promise;
    }

    sendPayInvoiceRequest(mode, card_info, address_info, invoice_id, invoice_auth) {
      const d = this.$q.defer();
      this.getLicInfo().then(() => {

        const params = {
          'callback': 'JSON_CALLBACK',
          'email': window.DP_PERSON_EMAIL,
          'mode': mode,
          'invoice_id': invoice_id,
          'invoice_auth': invoice_auth
        };

        if (mode === 'new') {
          params.cc_type      = card_info.type;
          params.cc_name      = card_info.name;
          params.cc_number    = card_info.number;
          params.cc_cv2       = card_info.cv2;
          params.cc_expire_yy = card_info.expire_yy;
          params.cc_expire_mm = card_info.expire_mm;

          params.addy_country   = address_info.country;
          params.addy_address   = address_info.address;
          params.addy_city      = address_info.city;
          params.addy_state     = address_info.state;
          params.addy_post_code = address_info.post_code;
        }

        return this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/pay-invoice', {
          params,
          timeout: 25000,
          cache: false
        }).then(x => d.resolve(x.data, x)
        , x => d.reject(x.data, x));
      }
      , x => d.reject(x));

      return d.promise;
    }

    openUpgradeLicense(upgradeType, options) {
      if (options == null) { options = {}; }
      const modalInstance = this.$modal.open({
        templateUrl: '/admin/load-view/License/upgrade-license-modal.html',
        controller: 'Admin_License_Ctrl_UpgradeLicenseModal',
        resolve: {
          upgradeType() {
            return upgradeType;
          },
          upgradeOptions() {
            return options;
          }
        }
      });
      return modalInstance.result;
    }

    openRenewLicense(options) {
      if (options == null) { options = {}; }
      const modalInstance = this.$modal.open({
        templateUrl: '/admin/load-view/License/upgrade-license-modal.html',
        controller: 'Admin_License_Ctrl_UpgradeLicenseModal',
        resolve: {
          upgradeType() {
            return 'extend';
          },
          upgradeOptions() {
            return options;
          }
        }
      });
      return modalInstance.result;
    }
  });
});