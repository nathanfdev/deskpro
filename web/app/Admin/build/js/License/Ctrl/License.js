(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_License_Ctrl_License, _ref;
    Admin_License_Ctrl_License = (function(_super) {
      __extends(Admin_License_Ctrl_License, _super);

      function Admin_License_Ctrl_License() {
        _ref = Admin_License_Ctrl_License.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_License_Ctrl_License.CTRL_ID = 'Admin_License_Ctrl_License';

      Admin_License_Ctrl_License.CTRL_AS = 'Ctrl';

      Admin_License_Ctrl_License.DEPS = [];

      Admin_License_Ctrl_License.prototype.init = function() {
        this.license = null;
        this.ma_token = null;
        this.ma_login_url = null;
        return this.lic_set_callback = null;
      };

      Admin_License_Ctrl_License.prototype.reloadLicData = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet({
          'lic_info': '/dp_license'
        }).then(function(res) {
          console.log(res);
          _this.license = res.data.lic_info.license;
          _this.ma_token = res.data.lic_info.ma_token;
          _this.ma_login_url = res.data.lic_info.ma_login_url;
          _this.lic_set_callback = res.data.lic_info.lic_set_callback;
          return _this.lic_code = _this.license.licenseCode;
        });
        return data_promise;
      };

      Admin_License_Ctrl_License.prototype.initialLoad = function() {
        return this.reloadLicData();
      };

      Admin_License_Ctrl_License.prototype.saveLicenseCode = function() {
        var postData,
          _this = this;
        postData = {
          license_code: this.license.license_code
        };
        this.startSpinner('saving');
        return this.Api.sendPost("dp_license", postData).success(function() {
          return _this.reloadLicData().then(function() {
            _this.stopSpinner('saving', true);
            return _this.Growl.success(_this.getRegisteredMessage('saved_lic'));
          });
        }).error(function() {
          return _this.Growl.error(_this.getRegisteredMessage('lic_error'));
        });
      };

      Admin_License_Ctrl_License.prototype.save = function() {};

      return Admin_License_Ctrl_License;

    })(Admin_Ctrl_Base);
    return Admin_License_Ctrl_License.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=License.js.map
*/