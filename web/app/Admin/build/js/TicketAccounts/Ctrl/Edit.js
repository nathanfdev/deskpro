(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/TicketAccounts/FormModel/EditTicketAccountModel'], function(Admin_Ctrl_Base, EditTicketAccountModel) {
    var Admin_TicketAccounts_Ctrl_Edit, _ref;
    Admin_TicketAccounts_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketAccounts_Ctrl_Edit, _super);

      function Admin_TicketAccounts_Ctrl_Edit() {
        _ref = Admin_TicketAccounts_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketAccounts_Ctrl_Edit.CTRL_ID = 'Admin_TicketAccounts_Ctrl_Edit';

      Admin_TicketAccounts_Ctrl_Edit.CTRL_AS = 'TicketAccountsEdit';

      Admin_TicketAccounts_Ctrl_Edit.DEPS = ['Api', 'Growl', 'DepartmentData', 'TicketAccountsData', '$stateParams', '$modal'];

      Admin_TicketAccounts_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_TicketAccounts_Ctrl_Edit.prototype.init = function() {};

      Admin_TicketAccounts_Ctrl_Edit.prototype.initialLoad = function() {
        var data_promise, dep_promise,
          _this = this;
        dep_promise = this.DepartmentData.loadDepList().then(function(departments) {
          return _this.deps = departments.values();
        });
        data_promise = this.Api.sendDataGet(['/ticket_accounts/' + this.$stateParams.id]).then(function(result) {
          _this.account = result.data.api_ticket_accounts_get.ticket_account;
          _this.form_model = new EditTicketAccountModel(_this.account);
          return _this.$scope.form = _this.form_model.form;
        });
        return this.$q.all([dep_promise, data_promise]);
      };

      Admin_TicketAccounts_Ctrl_Edit.prototype.testAccount = function() {
        var inst,
          _this = this;
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketAccounts/test-account-modal.html'),
          controller: 'Admin_TicketAccounts_Ctrl_TestIncomingAccount',
          resolve: {
            account_form: function() {
              return _this.form_model.getFormData();
            }
          }
        });
      };

      return Admin_TicketAccounts_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketAccounts_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/