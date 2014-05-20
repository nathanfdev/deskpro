(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Settings_Ctrl_PortalSettings;
    Admin_Settings_Ctrl_PortalSettings = (function(_super) {
      __extends(Admin_Settings_Ctrl_PortalSettings, _super);

      function Admin_Settings_Ctrl_PortalSettings() {
        return Admin_Settings_Ctrl_PortalSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_Settings_Ctrl_PortalSettings.CTRL_ID = 'Admin_Settings_Ctrl_PortalSettings';

      Admin_Settings_Ctrl_PortalSettings.CTRL_AS = 'Settings';

      Admin_Settings_Ctrl_PortalSettings.DEPS = [];

      Admin_Settings_Ctrl_PortalSettings.prototype.init = function() {
        return this.settings = null;
      };

      Admin_Settings_Ctrl_PortalSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'settings': '/portal_settings'
        }).then((function(_this) {
          return function(res) {
            _this.$scope.settings = res.data.settings.portal_settings;
            _this.$scope.show_ratings_opt = false;
            if (_this.$scope.settings.show_ratings > 0) {
              _this.$scope.show_ratings_opt = true;
            } else {
              _this.$scope.settings.show_ratings = 1;
            }
            return _this.settings = angular.copy(_this.$scope.settings);
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      Admin_Settings_Ctrl_PortalSettings.prototype.isDirtyState = function() {
        if (!this.settings) {
          return false;
        }
        if (!angular.equals(this.settings, this.$scope.settings)) {
          return true;
        } else {
          return false;
        }
      };

      Admin_Settings_Ctrl_PortalSettings.prototype.save = function() {
        var postData, promise;
        if (!this.$scope.show_ratings_opt) {
          this.$scope.settings.show_ratings = 0;
        }
        postData = {
          portal_settings: this.$scope.settings
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/portal_settings', postData).success((function(_this) {
          return function() {
            _this.settings = angular.copy(_this.$scope.settings);
            return _this.stopSpinner('saving').then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('portal_settings'));
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_Settings_Ctrl_PortalSettings;

    })(Admin_Ctrl_Base);
    return Admin_Settings_Ctrl_PortalSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=PortalSettings.js.map
