(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Settings_Ctrl_AdvancedSettings, _ref;
    Admin_Settings_Ctrl_AdvancedSettings = (function(_super) {
      __extends(Admin_Settings_Ctrl_AdvancedSettings, _super);

      function Admin_Settings_Ctrl_AdvancedSettings() {
        _ref = Admin_Settings_Ctrl_AdvancedSettings.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Settings_Ctrl_AdvancedSettings.CTRL_ID = 'Admin_Settings_Ctrl_AdvancedSettings';

      Admin_Settings_Ctrl_AdvancedSettings.CTRL_AS = 'Settings';

      Admin_Settings_Ctrl_AdvancedSettings.DEPS = [];

      Admin_Settings_Ctrl_AdvancedSettings.prototype.init = function() {
        return this.settings = null;
      };

      Admin_Settings_Ctrl_AdvancedSettings.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet({
          'settings': '/all_settings_raw'
        }).then(function(res) {
          return _this.$scope.settings = res.data.settings.all_settings;
        });
        return this.$q.all([data_promise]);
      };

      Admin_Settings_Ctrl_AdvancedSettings.prototype.save = function() {
        var postData, promise, setting, _i, _len, _ref1,
          _this = this;
        postData = {
          all_settings: {}
        };
        _ref1 = this.$scope.settings;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          setting = _ref1[_i];
          postData.all_settings[setting.name] = setting.value;
        }
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/all_settings_raw', postData).success(function() {
          return _this.stopSpinner('saving').then(function() {
            return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
          });
        }).error(function(info, code) {
          _this.stopSpinner('saving', true);
          return _this.applyErrorResponseToView(info);
        });
      };

      return Admin_Settings_Ctrl_AdvancedSettings;

    })(Admin_Ctrl_Base);
    return Admin_Settings_Ctrl_AdvancedSettings.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=AdvancedSettings.js.map
*/