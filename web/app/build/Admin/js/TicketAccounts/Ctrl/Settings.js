(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_TicketAccounts_Ctrl_Settings;
    Admin_TicketAccounts_Ctrl_Settings = (function(_super) {
      var _url;

      __extends(Admin_TicketAccounts_Ctrl_Settings, _super);

      function Admin_TicketAccounts_Ctrl_Settings() {
        return Admin_TicketAccounts_Ctrl_Settings.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketAccounts_Ctrl_Settings.CTRL_ID = 'Admin_TicketAccounts_Ctrl_Settings';

      Admin_TicketAccounts_Ctrl_Settings.CTRL_AS = 'Settings';

      Admin_TicketAccounts_Ctrl_Settings.DEPS = [];

      _url = '/email_accounts/settings';

      Admin_TicketAccounts_Ctrl_Settings.prototype.init = function() {
        return this.$scope.settings = null;
      };

      Admin_TicketAccounts_Ctrl_Settings.prototype.initialLoad = function() {
        return this.Api.sendGet(_url).then((function(_this) {
          return function(res) {
            _this.$scope.settings = res.data.email_settings;
            _this.$scope.maxUploadSize = res.data.max_filesize;
            if (_this.$scope.settings.attach_user_must_exts.length) {
              _this.$scope.attach_user_exts_limitmode = 'allow';
            } else if (_this.$scope.settings.attach_user_not_exts.length) {
              _this.$scope.attach_user_exts_limitmode = 'disallow';
            } else {
              _this.$scope.attach_user_exts_limitmode = 'any';
            }
            if (_this.$scope.settings.attach_agent_must_exts.length) {
              return _this.$scope.attach_agent_exts_limitmode = 'allow';
            } else if (_this.$scope.settings.attach_agent_not_exts.length) {
              return _this.$scope.attach_agent_exts_limitmode = 'disallow';
            } else {
              return _this.$scope.attach_agent_exts_limitmode = 'any';
            }
          };
        })(this));
      };

      Admin_TicketAccounts_Ctrl_Settings.prototype.save = function() {
        var postData;
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
          settings: this.$scope.settings
        };
        this.startSpinner('saving');
        return this.Api.sendPutJson(_url, postData).success((function(_this) {
          return function() {
            _this.stopSpinner('saving');
            return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_TicketAccounts_Ctrl_Settings;

    })(Admin_Ctrl_Base);
    return Admin_TicketAccounts_Ctrl_Settings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Settings.js.map
