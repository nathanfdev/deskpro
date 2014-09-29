(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'DeskPRO/Util/Arrays'], function(Admin_Ctrl_Base, Util, Arrays) {
    var Admin_TicketSettings_Ctrl_FwdSettings;
    Admin_TicketSettings_Ctrl_FwdSettings = (function(_super) {
      __extends(Admin_TicketSettings_Ctrl_FwdSettings, _super);

      function Admin_TicketSettings_Ctrl_FwdSettings() {
        return Admin_TicketSettings_Ctrl_FwdSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketSettings_Ctrl_FwdSettings.CTRL_ID = 'Admin_TicketSettings_Ctrl_FwdSettings';

      Admin_TicketSettings_Ctrl_FwdSettings.CTRL_AS = 'Fwd';

      Admin_TicketSettings_Ctrl_FwdSettings.DEPS = [];

      Admin_TicketSettings_Ctrl_FwdSettings.prototype.init = function() {
        this.email_accounts = [];
        return this.$scope.$watch('settings.use_account', (function(_this) {
          return function(accId) {
            var a;
            accId = parseInt(accId);
            a = Arrays.find(_this.email_accounts, function(a) {
              return a.id === accId;
            });
            return _this.$scope.use_account_address = a ? a.address : "noreply@example.com";
          };
        })(this));
      };

      Admin_TicketSettings_Ctrl_FwdSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'settings': '/ticket_settings/fwd',
          'accounts': '/email_accounts'
        }).then((function(_this) {
          return function(res) {
            _this.email_accounts = res.data.accounts.email_accounts;
            _this.$scope.settings = res.data.settings.ticket_fwd_settings;
            _this.settings = Util.clone(_this.$scope.settings);
            return _this.$scope.settings.use_account = (_this.$scope.settings.use_account || 0) + "";
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      Admin_TicketSettings_Ctrl_FwdSettings.prototype.isDirtyState = function() {
        if (!this.settings) {
          return false;
        }
        if (!equals.equals(this.settings, this.$scope.settings)) {
          return true;
        } else {
          return false;
        }
      };

      Admin_TicketSettings_Ctrl_FwdSettings.prototype.save = function() {
        var postData, promise;
        postData = {
          ticket_fwd_settings: this.$scope.settings
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/ticket_settings/fwd', postData).success((function(_this) {
          return function() {
            _this.settings = Util.clone(_this.$scope.settings);
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

      return Admin_TicketSettings_Ctrl_FwdSettings;

    })(Admin_Ctrl_Base);
    return Admin_TicketSettings_Ctrl_FwdSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=FwdSettings.js.map
