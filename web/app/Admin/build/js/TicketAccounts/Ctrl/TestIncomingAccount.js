(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketAccounts_Ctrl_TestIncomingAccount, _ref;
    Admin_TicketAccounts_Ctrl_TestIncomingAccount = (function(_super) {
      __extends(Admin_TicketAccounts_Ctrl_TestIncomingAccount, _super);

      function Admin_TicketAccounts_Ctrl_TestIncomingAccount() {
        _ref = Admin_TicketAccounts_Ctrl_TestIncomingAccount.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketAccounts_Ctrl_TestIncomingAccount.CTRL_AS = null;

      Admin_TicketAccounts_Ctrl_TestIncomingAccount.CTRL_ID = 'Admin_TicketAccounts_Ctrl_TestIncomingAccount';

      Admin_TicketAccounts_Ctrl_TestIncomingAccount.CTRL_TYPE = 'modal';

      Admin_TicketAccounts_Ctrl_TestIncomingAccount.DEPS = ['$modalInstance', 'account_form'];

      Admin_TicketAccounts_Ctrl_TestIncomingAccount.prototype.init = function() {
        var _this = this;
        this.$scope.dismiss = function() {
          return _this.$modalInstance.dismiss();
        };
        this.$scope.showLog = function() {
          return _this.$scope.showing_log = true;
        };
        this.$scope.is_testing = true;
        this.$scope.testNow = function() {
          return _this.testNow();
        };
        return this.testNow();
      };

      Admin_TicketAccounts_Ctrl_TestIncomingAccount.prototype.testNow = function() {
        var _this = this;
        this.$scope.showing_log = false;
        this.$scope.is_testing = true;
        return this.Api.sendPostJson('/ticket_accounts/test-account', this.account_form).success(function(result) {
          _this.$scope.is_testing = false;
          _this.$scope.is_success = result.is_success;
          _this.$scope.log = result.log;
          return _this.$scope.message_count = result.message_count;
        }).error(function() {
          _this.$scope.showing_log = true;
          _this.$scope.is_testing = false;
          _this.$scope.is_success = false;
          _this.$scope.log = "Server Error";
          return _this.$scope.message_count = 0;
        });
      };

      return Admin_TicketAccounts_Ctrl_TestIncomingAccount;

    })(Admin_Ctrl_Base);
    return Admin_TicketAccounts_Ctrl_TestIncomingAccount.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=TestIncomingAccount.js.map
*/