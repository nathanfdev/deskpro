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

      Admin_TicketAccounts_Ctrl_Edit.prototype.init = function() {
        this.didPassTest = false;
        this.testMessageCount = 0;
        return this.didConfirmExistingMessages = false;
      };

      Admin_TicketAccounts_Ctrl_Edit.prototype.initialLoad = function() {
        var data_promise, dep_promise,
          _this = this;
        dep_promise = this.DepartmentData.loadDepList().then(function(departments) {
          return _this.deps = departments.values();
        });
        if (!this.$stateParams.id) {
          this.account = {
            email_address: '',
            connection_type: 'pop3',
            in_pop3_account: {},
            in_imap_account: {},
            in_gmial_account: {},
            linked_transport: {
              transport_type: 'smtp',
              transport_options: {}
            }
          };
          this.form_model = new EditTicketAccountModel(this.account);
          this.$scope.form = this.form_model.form;
          return this.$q.all([dep_promise]);
        } else {
          data_promise = this.Api.sendDataGet(['/ticket_accounts/' + this.$stateParams.id]).then(function(result) {
            _this.account = result.data.api_ticket_accounts_get.ticket_account;
            _this.form_model = new EditTicketAccountModel(_this.account);
            return _this.$scope.form = _this.form_model.form;
          });
          return this.$q.all([dep_promise, data_promise]);
        }
      };

      Admin_TicketAccounts_Ctrl_Edit.prototype.testAccount = function() {
        var inst,
          _this = this;
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketAccounts/test-account-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              var testNow;
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              $scope.showLog = function() {
                return $scope.showing_log = true;
              };
              testNow = function() {
                $scope.showing_log = false;
                $scope.is_testing = true;
                return _this.loadAccountTest().success(function(result) {
                  $scope.is_testing = false;
                  $scope.is_success = result.is_success;
                  $scope.log = result.log;
                  return $scope.message_count = result.message_count;
                }).error(function() {
                  $scope.showing_log = true;
                  $scope.is_testing = false;
                  $scope.is_success = false;
                  $scope.log = "Server Error";
                  return $scope.message_count = 0;
                });
              };
              testNow();
              return $scope.testNow = function() {
                return testNow();
              };
            }
          ]
        });
      };

      Admin_TicketAccounts_Ctrl_Edit.prototype.loadAccountTest = function() {
        var _this = this;
        return this.Api.sendPostJson('/ticket_accounts/test-account', this.form_model.getFormData()).success(function(result) {
          return _this.didPassTest = result.is_success;
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