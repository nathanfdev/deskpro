(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_TicketAccounts_Ctrl_List, _ref;
    Admin_TicketAccounts_Ctrl_List = (function(_super) {
      __extends(Admin_TicketAccounts_Ctrl_List, _super);

      function Admin_TicketAccounts_Ctrl_List() {
        _ref = Admin_TicketAccounts_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketAccounts_Ctrl_List.CTRL_ID = 'Admin_TicketAccounts_Ctrl_List';

      Admin_TicketAccounts_Ctrl_List.CTRL_AS = 'TicketAccountsList';

      Admin_TicketAccounts_Ctrl_List.DEPS = ['$rootScope', '$scope', 'Api', 'Growl'];

      Admin_TicketAccounts_Ctrl_List.CTRL_TYPE = 'list';

      Admin_TicketAccounts_Ctrl_List.prototype.init = function() {};

      Admin_TicketAccounts_Ctrl_List.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet(['/ticket_accounts']).then(function(res) {
          return _this.accounts = res.data.api_ticket_accounts;
        });
        return this.$q.all([data_promise]);
      };

      return Admin_TicketAccounts_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_TicketAccounts_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/