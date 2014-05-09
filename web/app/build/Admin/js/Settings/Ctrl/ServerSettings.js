(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Settings_Ctrl_ServerSettings;
    Admin_Settings_Ctrl_ServerSettings = (function(_super) {
      __extends(Admin_Settings_Ctrl_ServerSettings, _super);

      function Admin_Settings_Ctrl_ServerSettings() {
        return Admin_Settings_Ctrl_ServerSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_Settings_Ctrl_ServerSettings.CTRL_ID = 'Admin_Settings_Ctrl_ServerSettings';

      Admin_Settings_Ctrl_ServerSettings.CTRL_AS = 'Settings';

      Admin_Settings_Ctrl_ServerSettings.DEPS = [];

      Admin_Settings_Ctrl_ServerSettings.prototype.init = function() {
        return this.settings = null;
      };

      Admin_Settings_Ctrl_ServerSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'settings': '/server_settings'
        }).then((function(_this) {
          return function(res) {
            _this.$scope.settings = res.data.settings.server_settings;
            return _this.settings = angular.copy(_this.$scope.settings);
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      Admin_Settings_Ctrl_ServerSettings.prototype.isDirtyState = function() {
        if (!this.settings) {
          return false;
        }
        if (!angular.equals(this.settings, this.$scope.settings)) {
          return true;
        } else {
          return false;
        }
      };

      Admin_Settings_Ctrl_ServerSettings.prototype.save = function() {
        var postData, promise;
        postData = {
          server_settings: this.$scope.settings
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/server_settings', postData).success((function(_this) {
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

      return Admin_Settings_Ctrl_ServerSettings;

    })(Admin_Ctrl_Base);
    return Admin_Settings_Ctrl_ServerSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerSettings.js.map
