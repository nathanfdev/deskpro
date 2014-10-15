(function() {
  define(function() {
    var Admin_License_Service_DpLicense;
    return Admin_License_Service_DpLicense = (function() {
      function Admin_License_Service_DpLicense(Api, $modal, $http, $q) {
        this.Api = Api;
        this.$modal = $modal;
        this.$http = $http;
        this.$q = $q;
      }

      Admin_License_Service_DpLicense.prototype.getLicInfo = function() {
        if (this.licGetting) {
          return this.licGetting;
        }
        return this.licGetting = this.Api.sendGet('/dp_license?basic=1').success((function(_this) {
          return function(data) {
            _this.licInfo = data.license;
            return _this.licInfo.licenseCode = _this.licInfo.licenseCode.replace(/\s/g, '');
          };
        })(this));
      };

      Admin_License_Service_DpLicense.prototype.getLicServerParams = function() {
        return {
          'license_id': this.licInfo.licenseId,
          'license_code': this.licInfo.licenseCode,
          'callback': 'JSON_CALLBACK',
          'email': window.DP_PERSON_EMAIL
        };
      };

      Admin_License_Service_DpLicense.prototype.getPlanUpgradeInfo = function() {
        var d;
        d = this.$q.defer();
        this.getLicInfo().then((function(_this) {
          return function() {
            return _this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/plan-info', {
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

      Admin_License_Service_DpLicense.prototype.sendPayInvoiceRequest = function(mode, card_info, invoice_id) {
        var d, params;
        params = this.getLicServerParams();
        params.mode = mode;
        params.invoice_id = invoice_id;
        if (mode === 'new') {
          params.cc_type = card_info.type;
          params.cc_name = card_info.name;
          params.cc_number = card_info.number;
          params.cc_cv2 = card_info.cv2;
          params.cc_expire_yy = card_info.expire_yy;
          params.cc_expire_mm = card_info.expire_mm;
        }
        d = this.$q.defer();
        this.getLicInfo().then((function(_this) {
          return function() {
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

      return Admin_License_Service_DpLicense;

    })();
  });

}).call(this);

//# sourceMappingURL=DpLicense.js.map
