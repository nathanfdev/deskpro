(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Settings_Ctrl_GeneralSettings, _ref;
    Admin_Settings_Ctrl_GeneralSettings = (function(_super) {
      __extends(Admin_Settings_Ctrl_GeneralSettings, _super);

      function Admin_Settings_Ctrl_GeneralSettings() {
        _ref = Admin_Settings_Ctrl_GeneralSettings.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Settings_Ctrl_GeneralSettings.CTRL_ID = 'Admin_Settings_Ctrl_GeneralSettings';

      Admin_Settings_Ctrl_GeneralSettings.CTRL_AS = 'Settings';

      Admin_Settings_Ctrl_GeneralSettings.DEPS = [];

      Admin_Settings_Ctrl_GeneralSettings.prototype.init = function() {
        return this.settings = {
          default_timezone: 'UTC',
          task_reminder_time: '09:30'
        };
      };

      Admin_Settings_Ctrl_GeneralSettings.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet({
          'settings': '/general_settings'
        }).then(function(res) {
          _this.$scope.settings = res.data.settings.general_settings;
          _this.$scope.maxUploadSize = res.data.settings.max_filesize;
          _this.settings = angular.copy(_this.$scope.settings);
          if (_this.settings.attach_user_must_exts.length) {
            _this.$scope.attach_user_exts_limitmode = 'allow';
          } else if (_this.settings.attach_user_not_exts.length) {
            _this.$scope.attach_user_exts_limitmode = 'disallow';
          } else {
            _this.$scope.attach_user_exts_limitmode = 'any';
          }
          if (_this.settings.attach_agent_must_exts.length) {
            return _this.$scope.attach_agent_exts_limitmode = 'allow';
          } else if (_this.settings.attach_agent_not_exts.length) {
            return _this.$scope.attach_agent_exts_limitmode = 'disallow';
          } else {
            return _this.$scope.attach_agent_exts_limitmode = 'any';
          }
        });
        return this.$q.all([data_promise]);
      };

      Admin_Settings_Ctrl_GeneralSettings.prototype.isDirtyState = function() {
        if (!this.settings) {
          return false;
        }
        if (!angular.equals(this.settings, this.$scope.settings)) {
          return true;
        } else {
          return false;
        }
      };

      Admin_Settings_Ctrl_GeneralSettings.prototype.save = function() {
        var postData, promise,
          _this = this;
        if (this.$scope.attach_user_exts_limitmode === 'allow') {
          this.$scope.settings.attach_user_not_exts = [];
        } else if (this.$scope.attach_user_exts_limitmode === 'disallow') {
          this.$scope.settings.attach_user_must_exts = [];
        } else {
          this.$scope.settings.attach_user_not_exts = [];
          this.$scope.settings.attach_user_must_exts = [];
        }
        if (this.$scope.attach_agent_exts_limitmode === 'allow') {
          this.$scope.settings.attach_agent_not_exts = [];
        } else if (this.$scope.attach_agent_exts_limitmode === 'disallow') {
          this.$scope.settings.attach_agent_must_exts = [];
        } else {
          this.$scope.settings.attach_agent_not_exts = [];
          this.$scope.settings.attach_agent_must_exts = [];
        }
        postData = {
          general_settings: this.$scope.settings
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/general_settings', postData).success(function() {
          _this.settings = angular.copy(_this.$scope.settings);
          return _this.stopSpinner('saving').then(function() {
            return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
          });
        }).error(function(info, code) {
          _this.stopSpinner('saving', true);
          return _this.applyErrorResponseToView(info);
        });
      };

      return Admin_Settings_Ctrl_GeneralSettings;

    })(Admin_Ctrl_Base);
    return Admin_Settings_Ctrl_GeneralSettings.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=GeneralSettings.js.map
*/