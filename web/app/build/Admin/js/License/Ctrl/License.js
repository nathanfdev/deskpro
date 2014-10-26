(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_License_Ctrl_License;
    Admin_License_Ctrl_License = (function(_super) {
      __extends(Admin_License_Ctrl_License, _super);

      function Admin_License_Ctrl_License() {
        return Admin_License_Ctrl_License.__super__.constructor.apply(this, arguments);
      }

      Admin_License_Ctrl_License.CTRL_ID = 'Admin_License_Ctrl_License';

      Admin_License_Ctrl_License.CTRL_AS = 'Ctrl';

      Admin_License_Ctrl_License.DEPS = ['$window', 'DpLicense'];

      Admin_License_Ctrl_License.prototype.init = function() {
        var old_title;
        this.license = null;
        this.ma_token = null;
        this.ma_login_url = null;
        this.lic_set_callback = null;
        this.$scope.$watch('lic_code', (function(_this) {
          return function(lic_code) {
            lic_code = lic_code || '';
            lic_code = lic_code.replace(/\s/g, '');
            lic_code = (lic_code.match(/(.{1,50})/g) || [lic_code]).join("\n");
            return _this.$scope.lic_code = lic_code;
          };
        })(this));
        old_title = window.document.title;
        window.document.title = 'DeskPRO Billing Interface';
        return this.$scope.$on('$destroy', function() {
          return window.document.title = old_title;
        });
      };

      Admin_License_Ctrl_License.prototype.reloadLicData = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'lic_info': '/dp_license'
        }).then((function(_this) {
          return function(res) {
            _this.license = res.data.lic_info.license;
            _this.ma_token = res.data.lic_info.ma_token;
            _this.ma_login_url = res.data.lic_info.ma_login_url;
            _this.lic_set_callback = res.data.lic_info.lic_set_callback;
            _this.$scope.lic_code = _this.license.licenseCode;
            _this.$scope.refreshing_lic = true;
            return _this.DpLicense.getNewLicenseKey().then(function(res) {
              var cmp1, cmp2;
              cmp1 = res.license_code.replace(/[^a-zA-Z0-9]/g, '');
              cmp2 = _this.license.licenseCode.replace(/[^a-zA-Z0-9]/g, '');
              if (cmp1 === cmp2) {
                return _this.$scope.refreshing_lic = false;
              } else {
                return _this.DpLicense.setNewLicenseCode(res.license_code).then(function() {
                  var lic_code;
                  _this.$scope.refreshing_lic = false;
                  lic_code = res.license_code;
                  lic_code = lic_code.replace(/\s/g, '');
                  lic_code = (lic_code.match(/(.{1,50})/g) || [lic_code]).join("\n");
                  _this.$scope.lic_code = lic_code;
                  return _this.license.licenseCode = lic_code;
                }, function() {
                  return _this.$scope.refreshing_lic = false;
                });
              }
            }, function() {
              return _this.$scope.refreshing_lic = false;
            });
          };
        })(this));
        return data_promise;
      };

      Admin_License_Ctrl_License.prototype.initialLoad = function() {
        return this.reloadLicData();
      };

      Admin_License_Ctrl_License.prototype.downloadKeyfile = function() {
        return this.$window.location = this.Api.formatUrl('dp_license/keyfile.txt') + '?API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN;
      };

      Admin_License_Ctrl_License.prototype.goToMembersArea = function() {
        this.$window.location = 'https://www.deskpro.com/members/';
      };

      Admin_License_Ctrl_License.prototype.saveLicenseCode = function() {
        var postData;
        postData = {
          license_code: this.$scope.lic_code
        };
        this.$scope.lic_error_code = false;
        this.$scope.show_lic_error = false;
        this.startSpinner('saving');
        return this.Api.sendPost("dp_license", postData).success((function(_this) {
          return function() {
            return _this.reloadLicData().then(function() {
              return _this.stopSpinner('saving').then(function() {
                return _this.Growl.success(_this.getRegisteredMessage('saved_lic'));
              });
            });
          };
        })(this)).error((function(_this) {
          return function(data) {
            _this.stopSpinner('saving', true);
            _this.$scope.lic_error_code = false;
            if (data && data.error_code) {
              _this.$scope.lic_error_code = data.error_code;
            }
            return _this.$scope.show_lic_error = true;
          };
        })(this));
      };

      Admin_License_Ctrl_License.prototype.save = function() {};

      Admin_License_Ctrl_License.prototype.openUpgradeLicense = function() {
        return this.DpLicense.openUpgradeLicense('add_agents').then((function(_this) {
          return function() {
            return _this.$state.go('license_go');
          };
        })(this));
      };

      Admin_License_Ctrl_License.prototype.openRenewLicense = function() {
        return this.DpLicense.openRenewLicense().then((function(_this) {
          return function() {
            return _this.$state.go('license_go');
          };
        })(this));
      };

      return Admin_License_Ctrl_License;

    })(Admin_Ctrl_Base);
    return Admin_License_Ctrl_License.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=License.js.map
