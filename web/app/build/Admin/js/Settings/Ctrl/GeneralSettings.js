(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Settings_Ctrl_GeneralSettings;
    Admin_Settings_Ctrl_GeneralSettings = (function(_super) {
      __extends(Admin_Settings_Ctrl_GeneralSettings, _super);

      function Admin_Settings_Ctrl_GeneralSettings() {
        return Admin_Settings_Ctrl_GeneralSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_Settings_Ctrl_GeneralSettings.CTRL_ID = 'Admin_Settings_Ctrl_GeneralSettings';

      Admin_Settings_Ctrl_GeneralSettings.CTRL_AS = 'Settings';

      Admin_Settings_Ctrl_GeneralSettings.DEPS = ['$http'];

      Admin_Settings_Ctrl_GeneralSettings.prototype.init = function() {
        this.settings = {
          default_timezone: 'UTC',
          task_reminder_time: '09:30',
          attach_agent_must_exts: [],
          attach_agent_not_exts: [],
          attach_user_must_exts: [],
          attach_user_not_exts: []
        };
        return this.$scope.settings = this.settings;
      };

      Admin_Settings_Ctrl_GeneralSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'settings': '/general_settings',
          'email_accounts': '/email_accounts'
        }).then((function(_this) {
          return function(res) {
            _this.$scope.settings = res.data.settings.general_settings;
            _this.$scope.maxUploadSize = res.data.settings.max_filesize;
            _this.settings = angular.copy(_this.$scope.settings);
            _this.$scope.email_accounts = res.data.email_accounts.email_accounts;
            _this.$scope.email_accounts = _this.$scope.email_accounts.filter(function(x) {
              return x.outgoing_account_type !== null;
            });
            if (_this.$scope.email_accounts.length) {
              if (!_this.$scope.settings.default_from_email || !_this.$scope.email_accounts.filter(function(x) {
                return x.address === _this.$scope.settings.default_from_email;
              }).length) {
                _this.$scope.settings.default_from_email = _this.$scope.email_accounts[0].address;
              }
            }
            if (_this.settings.attach_user_must_exts.length) {
              _this.$scope.attach_user_exts_limitmode = 'allow';
            } else if (_this.settings.attach_user_not_exts.length) {
              _this.$scope.attach_user_exts_limitmode = 'disallow';
            } else {
              _this.$scope.attach_user_exts_limitmode = 'any';
            }
            if (_this.settings.attach_agent_must_exts.length) {
              _this.$scope.attach_agent_exts_limitmode = 'allow';
            } else if (_this.settings.attach_agent_not_exts.length) {
              _this.$scope.attach_agent_exts_limitmode = 'disallow';
            } else {
              _this.$scope.attach_agent_exts_limitmode = 'any';
            }
            return _this.orig_url = _this.$scope.settings.deskpro_url || null;
          };
        })(this));
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
        var pingUrl, postData, promise;
        this.$scope.url_error = false;
        this.startSpinner('saving');
        if (this.orig_url && this.orig_url !== this.$scope.settings.deskpro_url) {
          this.$scope.settings.deskpro_url = this.$scope.settings.deskpro_url.replace(/\/?index\.php$/, '').replace(/\/+$/, '');
          this.$scope.settings.deskpro_url += '/';
          pingUrl = this.$scope.settings.deskpro_url + 'index.php?_sys=ping&type=jsonp&callback=JSON_CALLBACK';
          this.$http.jsonp(pingUrl).success((function(_this) {
            return function() {
              _this.orig_url = _this.$scope.settings.deskpro_url;
              return _this.save();
            };
          })(this)).error((function(_this) {
            return function() {
              _this.$scope.url_error = true;
              _this.stopSpinner('saving', true);
              return _this.showAlert("We detected that the Helpdesk URL that you entered is invalid. Please double-check the URL and try again.");
            };
          })(this));
          return;
        }
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
        return promise = this.Api.sendPostJson('/general_settings', postData).success((function(_this) {
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

      return Admin_Settings_Ctrl_GeneralSettings;

    })(Admin_Ctrl_Base);
    return Admin_Settings_Ctrl_GeneralSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=GeneralSettings.js.map
