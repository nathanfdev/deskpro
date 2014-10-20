(function() {
  define(function() {
    var Admin_License_Service_DpLicense;
    return Admin_License_Service_DpLicense = (function() {
      function Admin_License_Service_DpLicense(Api, $modal, $http, $q) {
        this.Api = Api;
        this.$modal = $modal;
        this.$http = $http;
        this.$q = $q;
        this.shortAuthCode = null;
      }

      Admin_License_Service_DpLicense.prototype.getLicInfo = function(reload) {
        if (this.licGetting && !reload) {
          return this.licGetting.promise;
        }
        this.licGetting = this.$q.defer();
        this.Api.sendGet('/dp_license').success((function(_this) {
          return function(data) {
            _this.info = data;
            _this.licInfo = data.license;
            _this.licInfo.licenseCode = _this.licInfo.licenseCode.replace(/\s/g, '');
            return _this.licGetting.resolve(_this.info);
          };
        })(this), (function(_this) {
          return function() {
            _this.licGetting.reject();
            return _this.licGetting = null;
          };
        })(this));
        return this.licGetting.promise;
      };

      Admin_License_Service_DpLicense.prototype.getLicServerParams = function() {
        return {
          'license_id': this.licInfo.licenseId,
          'license_code': this.licInfo.licenseCode,
          'callback': 'JSON_CALLBACK',
          'email': window.DP_PERSON_EMAIL
        };
      };

      Admin_License_Service_DpLicense.prototype.getPlanUpgradeInfo = function(num_agents) {
        var d;
        d = this.$q.defer();
        this.getLicInfo().then((function(_this) {
          return function() {
            var params;
            params = _this.getLicServerParams();
            params.num_agents = num_agents || 0;
            return _this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/plan-info', {
              params: params,
              timeout: 25000,
              cache: false
            }).then(function(x) {
              return d.resolve(x.data, x);
            }, function(x) {
              return d.reject(x.data, x);
            });
          };
        })(this), function(x) {
          return d.reject(x);
        });
        return d.promise;
      };

      Admin_License_Service_DpLicense.prototype.getRenewInfo = function(num_years) {
        var d;
        d = this.$q.defer();
        this.getLicInfo().then((function(_this) {
          return function() {
            var params;
            params = _this.getLicServerParams();
            params.num_years = num_years || 0;
            return _this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/renew-info', {
              params: params,
              timeout: 25000,
              cache: false
            }).then(function(x) {
              return d.resolve(x.data, x);
            }, function(x) {
              return d.reject(x.data, x);
            });
          };
        })(this), function(x) {
          return d.reject(x);
        });
        return d.promise;
      };

      Admin_License_Service_DpLicense.prototype.getNewLicenseKey = function() {
        var d;
        d = this.$q.defer();
        this.getLicInfo().then((function(_this) {
          return function() {
            return _this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/renew-key', {
              params: _this.getLicServerParams(),
              timeout: 25000,
              cache: false
            }).then(function(x) {
              return d.resolve(x.data, x);
            }, function(x) {
              return d.reject(x.data, x);
            });
          };
        })(this), function(x) {
          return d.reject(x);
        });
        return d.promise;
      };

      Admin_License_Service_DpLicense.prototype.setNewLicenseCode = function(lic_code) {
        var d, postData;
        postData = {
          license_code: lic_code
        };
        d = this.$q.defer();
        this.Api.sendPost("dp_license", postData).success((function(_this) {
          return function() {
            _this.getLicInfo(true);
            return d.resolve({
              success: true,
              lic_code: lic_code
            });
          };
        })(this)).error((function(_this) {
          return function(data) {
            return d.reject({
              success: false,
              lic_code: lic_code,
              error_code: data != null ? data.error_code : void 0
            });
          };
        })(this));
        return d.promise;
      };

      Admin_License_Service_DpLicense.prototype.sendPayInvoiceRequest = function(mode, card_info, address_info, invoice_id, invoice_auth) {
        var d;
        d = this.$q.defer();
        this.getLicInfo().then((function(_this) {
          return function() {
            var params;
            params = {
              'callback': 'JSON_CALLBACK',
              'email': window.DP_PERSON_EMAIL,
              'mode': mode,
              'invoice_id': invoice_id,
              'invoice_auth': invoice_auth
            };
            if (mode === 'new') {
              params.cc_type = card_info.type;
              params.cc_name = card_info.name;
              params.cc_number = card_info.number;
              params.cc_cv2 = card_info.cv2;
              params.cc_expire_yy = card_info.expire_yy;
              params.cc_expire_mm = card_info.expire_mm;
              params.addy_country = address_info.country;
              params.addy_address = address_info.address;
              params.addy_city = address_info.city;
              params.addy_state = address_info.state;
              params.addy_post_code = address_info.post_code;
            }
            return _this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/pay-invoice', {
              params: params,
              timeout: 25000,
              cache: false
            }).then(function(x) {
              return d.resolve(x.data, x);
            }, function(x) {
              return d.reject(x.data, x);
            });
          };
        })(this), function(x) {
          return d.reject(x);
        });
        return d.promise;
      };

      Admin_License_Service_DpLicense.prototype.openUpgradeLicense = function(upgradeType, options) {
        var modalInstance;
        if (options == null) {
          options = {};
        }
        modalInstance = this.$modal.open({
          templateUrl: '/admin/load-view/License/upgrade-license-modal.html',
          controller: 'Admin_License_Ctrl_UpgradeLicenseModal',
          resolve: {
            upgradeType: function() {
              return upgradeType;
            },
            upgradeOptions: function() {
              return options;
            }
          }
        });
        return modalInstance.result;
      };

      Admin_License_Service_DpLicense.prototype.openRenewLicense = function(options) {
        var modalInstance;
        if (options == null) {
          options = {};
        }
        modalInstance = this.$modal.open({
          templateUrl: '/admin/load-view/License/upgrade-license-modal.html',
          controller: 'Admin_License_Ctrl_UpgradeLicenseModal',
          resolve: {
            upgradeType: function() {
              return 'extend';
            },
            upgradeOptions: function() {
              return options;
            }
          }
        });
        return modalInstance.result;
      };

      return Admin_License_Service_DpLicense;

    })();
  });

}).call(this);

//# sourceMappingURL=DpLicense.js.map
