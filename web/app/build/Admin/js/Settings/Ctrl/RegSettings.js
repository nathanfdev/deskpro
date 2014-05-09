(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Settings_Ctrl_RegSettings;
    Admin_Settings_Ctrl_RegSettings = (function(_super) {
      __extends(Admin_Settings_Ctrl_RegSettings, _super);

      function Admin_Settings_Ctrl_RegSettings() {
        return Admin_Settings_Ctrl_RegSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_Settings_Ctrl_RegSettings.CTRL_ID = 'Admin_Settings_Ctrl_RegSettings';

      Admin_Settings_Ctrl_RegSettings.CTRL_AS = 'Settings';

      Admin_Settings_Ctrl_RegSettings.DEPS = [];

      Admin_Settings_Ctrl_RegSettings.prototype.init = function() {
        return this.settings = null;
      };

      Admin_Settings_Ctrl_RegSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'settings': '/registration_settings'
        }).then((function(_this) {
          return function(res) {
            _this.$scope.settings = res.data.settings.registration_settings;
            console.log(_this.$scope.settings);
            return _this.settings = angular.copy(_this.$scope.settings);
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      Admin_Settings_Ctrl_RegSettings.prototype.isDirtyState = function() {
        if (!this.settings) {
          return false;
        }
        if (!angular.equals(this.settings, this.$scope.settings)) {
          return true;
        } else {
          return false;
        }
      };

      Admin_Settings_Ctrl_RegSettings.prototype.save = function() {
        var postData, promise;
        postData = {
          registration_settings: this.$scope.settings
        };
        if (postData.registration_settings.reg_enabled === "1" || postData.registration_settings.reg_enabled === 1 || postData.registration_settings.reg_enabled === true) {
          postData.registration_settings.reg_enabled = true;
        } else {
          postData.registration_settings.reg_enabled = false;
        }
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/registration_settings', postData).success((function(_this) {
          return function() {
            _this.settings = angular.copy(_this.$scope.settings);
            return _this.stopSpinner('saving').then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_Settings_Ctrl_RegSettings;

    })(Admin_Ctrl_Base);
    return Admin_Settings_Ctrl_RegSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=RegSettings.js.map
