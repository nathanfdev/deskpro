(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Util) {
    var Admin_Settings_Ctrl_PasswordSettings;
    Admin_Settings_Ctrl_PasswordSettings = (function(_super) {
      __extends(Admin_Settings_Ctrl_PasswordSettings, _super);

      function Admin_Settings_Ctrl_PasswordSettings() {
        return Admin_Settings_Ctrl_PasswordSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_Settings_Ctrl_PasswordSettings.CTRL_ID = 'Admin_Settings_Ctrl_PasswordSettings';

      Admin_Settings_Ctrl_PasswordSettings.CTRL_AS = 'Settings';

      Admin_Settings_Ctrl_PasswordSettings.prototype.init = function() {
        return this.$scope.password_settings = {};
      };

      Admin_Settings_Ctrl_PasswordSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'settings': '/password_settings'
        }).then((function(_this) {
          return function(res) {
            _this.$scope.settings = {
              sessions_lifetime: res.data.settings.settings.sessions_lifetime,
              session_keepalive_require_page: res.data.settings.settings.session_keepalive_require_page,
              ip_security_enabled: res.data.settings.settings.ip_security_enabled,
              ip_security_mode: res.data.settings.settings.ip_security_mode || 'admins',
              ip_security_whitelist_lifetime: res.data.settings.settings.ip_security_whitelist_lifetime + "",
              disable_notifications: res.data.settings.settings.disable_notifications,
              enable_agent_rememberme: res.data.settings.settings.enable_agent_rememberme,
              enable_user_rememberme: res.data.settings.settings.enable_user_rememberme
            };
            _this.$scope.agent = res.data.settings.settings.agent;
            _this.$scope.user = res.data.settings.settings.user;
            _this.$scope.agent.standard_policy = _this.$scope.agent.min_length === 5 && !_this.$scope.agent.max_age && !_this.$scope.agent.forbid_reuse && !_this.$scope.agent.require_num_uppercase && !_this.$scope.agent.require_num_lowercase && !_this.$scope.agent.require_num_number && !_this.$scope.agent.require_num_symbol;
            return _this.$scope.user.standard_policy = _this.$scope.user.min_length === 5 && !_this.$scope.user.max_age && !_this.$scope.user.forbid_reuse && !_this.$scope.user.require_num_uppercase && !_this.$scope.user.require_num_lowercase && !_this.$scope.user.require_num_number && !_this.$scope.user.require_num_symbol;
          };
        })(this));
        return data_promise;
      };

      Admin_Settings_Ctrl_PasswordSettings.prototype.saveSettings = function() {
        var settings;
        if (this.$scope.form_props.$invalid) {
          return;
        }
        this.startSpinner('saving');
        settings = this.$scope.settings;
        settings.agent = Util.clone(this.$scope.agent, true);
        settings.user = Util.clone(this.$scope.user, true);
        if (settings.agent.standard_policy) {
          settings.agent.min_length = 5;
          settings.agent.max_age = 0;
          settings.agent.forbid_reuse = false;
          settings.agent.require_num_uppercase = 0;
          settings.agent.require_num_lowercase = 0;
          settings.agent.require_num_number = 0;
          settings.agent.require_num_symbol = 0;
        }
        if (settings.user.standard_policy) {
          settings.user.min_length = 5;
          settings.user.max_age = 0;
          settings.user.forbid_reuse = false;
          settings.user.require_num_uppercase = 0;
          settings.user.require_num_lowercase = 0;
          settings.user.require_num_number = 0;
          settings.user.require_num_symbol = 0;
        }
        delete settings.agent.standard_policy;
        delete settings.user.standard_policy;
        return this.Api.sendPostJson('/password_settings', {
          settings: settings
        }).success((function(_this) {
          return function() {
            return _this.stopSpinner('saving').then(function() {
              var message;
              message = _this.getRegisteredMessage('saved_settings');
              if (message && message.length) {
                return _this.Growl.success(message);
              }
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            return _this.stopSpinner('saving', true);
          };
        })(this));
      };

      return Admin_Settings_Ctrl_PasswordSettings;

    })(Admin_Ctrl_Base);
    return Admin_Settings_Ctrl_PasswordSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=PasswordSettings.js.map
