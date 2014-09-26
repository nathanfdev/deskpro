(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Main_Ctrl_Base) {
    var Admin_ChannelSms_Ctrl_Accounts;
    return Admin_ChannelSms_Ctrl_Accounts = (function(_super) {
      __extends(Admin_ChannelSms_Ctrl_Accounts, _super);

      function Admin_ChannelSms_Ctrl_Accounts() {
        return Admin_ChannelSms_Ctrl_Accounts.__super__.constructor.apply(this, arguments);
      }

      Admin_ChannelSms_Ctrl_Accounts.CTRL_ID = 'Admin_ChannelSms_Ctrl_List';

      Admin_ChannelSms_Ctrl_Accounts.CTRL_AS = 'ChannelSmsList';

      Admin_ChannelSms_Ctrl_Accounts.DEPS = ['SmsAccountsData'];

      Admin_ChannelSms_Ctrl_Accounts.prototype.init = function() {
        console.log('initieddd');
        return this.accounts = [];
      };

      Admin_ChannelSms_Ctrl_Accounts.prototype.initialLoad = function() {
        var list_promise;
        list_promise = this.SmsAccountsData.loadList().then((function(_this) {
          return function(recs) {
            console.log(recs);
            _this.accounts = recs.values();
            return console.log(_this.accounts);
          };
        })(this));
        return this.$q.all([list_promise]);
      };

      return Admin_ChannelSms_Ctrl_Accounts;

    })(Admin_Main_Ctrl_Base);
  });

}).call(this);

//# sourceMappingURL=Accounts.js.map
