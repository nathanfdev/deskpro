(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Settings_Ctrl_EmailSettings, _ref;
    Admin_Settings_Ctrl_EmailSettings = (function(_super) {
      __extends(Admin_Settings_Ctrl_EmailSettings, _super);

      function Admin_Settings_Ctrl_EmailSettings() {
        _ref = Admin_Settings_Ctrl_EmailSettings.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Settings_Ctrl_EmailSettings.CTRL_ID = 'Admin_Settings_Ctrl_EmailSettings';

      Admin_Settings_Ctrl_EmailSettings.CTRL_AS = 'Settings';

      Admin_Settings_Ctrl_EmailSettings.DEPS = [];

      Admin_Settings_Ctrl_EmailSettings.prototype.init = function() {
        return this.settings = null;
      };

      Admin_Settings_Ctrl_EmailSettings.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet({
          'settings': '/email_settings'
        }).then(function(res) {
          _this.$scope.settings = res.data.settings.email_settings;
          if (_this.$scope.settings.gateway_max_email === 0) {
            _this.$scope.settings.gateway_max_email_mb = '';
          } else {
            _this.$scope.settings.gateway_max_email_mb = _this.$scope.settings.gateway_max_email / 1024;
          }
          return _this.settings = angular.copy(_this.$scope.settings);
        });
        return this.$q.all([data_promise]);
      };

      Admin_Settings_Ctrl_EmailSettings.prototype.isDirtyState = function() {
        if (!this.settings) {
          return false;
        }
        if (!angular.equals(this.settings, this.$scope.settings)) {
          return true;
        } else {
          return false;
        }
      };

      Admin_Settings_Ctrl_EmailSettings.prototype.save = function() {
        var postData, promise,
          _this = this;
        this.$scope.settings.gateway_max_email = this.$scope.settings.gateway_max_email_mb * 1024;
        postData = {
          email_settings: this.$scope.settings
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/email_settings', postData).success(function() {
          _this.settings = angular.copy(_this.$scope.settings);
          return _this.stopSpinner('saving').then(function() {
            return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
          });
        }).error(function(info, code) {
          _this.stopSpinner('saving', true);
          return _this.applyErrorResponseToView(info);
        });
      };

      return Admin_Settings_Ctrl_EmailSettings;

    })(Admin_Ctrl_Base);
    return Admin_Settings_Ctrl_EmailSettings.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EmailSettings.js.map
*/