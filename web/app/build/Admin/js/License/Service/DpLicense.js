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
          'callback': 'JSON_CALLBACK'
        };
      };

      Admin_License_Service_DpLicense.prototype.getPlanUpgradeInfo = function() {
        var d;
        d = this.$q.defer();
        this.getLicInfo().then((function(_this) {
          return function() {
            return _this.$http.jsonp(DP_SECURE_LIC_SERVER + '/api/license/plan-info.json', {
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
