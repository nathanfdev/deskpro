(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['AdminStart/Ctrl/StartBase', 'Admin/TicketAccounts/FormModel/EditTicketAccountModel'], function(StartBase, EditTicketAccountModel) {
    var AdminStart_Ctrl_Email;
    AdminStart_Ctrl_Email = (function(_super) {
      __extends(AdminStart_Ctrl_Email, _super);

      function AdminStart_Ctrl_Email() {
        return AdminStart_Ctrl_Email.__super__.constructor.apply(this, arguments);
      }

      AdminStart_Ctrl_Email.CTRL_ID = 'AdminStart_Ctrl_Email';

      AdminStart_Ctrl_Email.DEPS = ['$modal', '$location'];

      AdminStart_Ctrl_Email.prototype.init = function() {
        this.$scope.TicketAccountsEdit = this;
        this.test_email = {
          to: '',
          from: '',
          subject: 'Test email',
          message: 'This is a test. If you see this email in your inbox, your outgoing email account are correct.'
        };
        this.account = {
          email_address: '',
          connection_type: '',
          other_addresses: [],
          in_pop3_account: {},
          in_imap_account: {},
          in_gmial_account: {},
          linked_transport: {
            transport_type: '',
            transport_options: {}
          }
        };
        this.form_model = new EditTicketAccountModel(this.account, [], {});
        this.form_model.form.incoming_account_type = '';
        this.form_model.form.outgoing_account_type = 'smtp';
        return this.$scope.form = this.form_model.form;
      };


      /*
        	 * Saves the current form
        	 *
        	 * @return {promise}
       */

      AdminStart_Ctrl_Email.prototype.saveAndContinue = function() {
        var postData, promise;
        this.$scope.email_is_error = null;
        postData = this.form_model.getFormData();
        if (!postData.address || postData.address.length < 3 || postData.address.indexOf('@') === -1) {
          this.$scope.email_is_error = 'invalid_email';
          return;
        }
        this.$scope.is_loading = true;
        promise = this.Api.sendPutJson('/email_accounts', postData);
        promise.success((function(_this) {
          return function(result) {
            _this.account.id = result.email_account_id || _this.account.id;
            _this.account.is_enabled = true;
            return _this.$location.path('/finish');
          };
        })(this));
        promise.error((function(_this) {
          return function(info, code) {
            _this.$scope.is_loading = false;
            return _this.$scope.email_is_error = 'general';
          };
        })(this));
        return promise;
      };


      /*
        	 * Test current account settings
        	 *
        	 * @return {promise}
       */

      AdminStart_Ctrl_Email.prototype.loadAccountTest = function() {
        return this.Api.sendPostJson('/email_accounts/test-account', this.form_model.getFormData()).success((function(_this) {
          return function(result) {
            return _this.didPassTest = result.is_success;
          };
        })(this));
      };


      /*
        	 * Test current outgoing settings with message details from @test_email object.
        	 *
        	 * @return {promise}
       */

      AdminStart_Ctrl_Email.prototype.loadOutgoingAccountTest = function() {
        var form_data;
        form_data = this.form_model.getFormData();
        form_data.test_email = this.test_email;
        return this.Api.sendPostJson('/email_accounts/test-outgoing-account', form_data);
      };


      /*
        	 * Show the test account modal
       */

      AdminStart_Ctrl_Email.prototype.testAccountModal = function() {
        var inst;
        return inst = this.$modal.open({
          templateUrl: 'AdminInterface/TicketAccounts/test-account-modal.html',
          controller: [
            '$scope', '$modalInstance', (function(_this) {
              return function($scope, $modalInstance) {
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
              };
            })(this)
          ]
        });
      };


      /*
        	 * Show the test account modal
       */

      AdminStart_Ctrl_Email.prototype.testOutgoingModal = function() {
        var inst, me;
        me = this;
        return inst = this.$modal.open({
          templateUrl: 'AdminInterface/TicketAccounts/test-outgoing-modal.html',
          resolve: {
            test_email: (function(_this) {
              return function() {
                _this.test_email.from = _this.form_model.form.address;
                return _this.test_email;
              };
            })(this)
          },
          controller: [
            '$scope', '$modalInstance', 'test_email', (function(_this) {
              return function($scope, $modalInstance, test_email) {
                var resetTest, testNow;
                $scope.dismiss = function() {
                  return $modalInstance.dismiss();
                };
                $scope.showLog = function() {
                  return $scope.showing_log = true;
                };
                $scope.test_email = test_email;
                testNow = function() {
                  $scope.testing_started = true;
                  $scope.showing_log = false;
                  $scope.is_testing = true;
                  if (!test_email.from) {
                    test_email.from = me.form_model.form.address;
                  }
                  return me.loadOutgoingAccountTest().success(function(result) {
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
                resetTest = function() {
                  return $scope.testing_started = false;
                };
                $scope.testNow = function() {
                  return testNow();
                };
                return $scope.resetTest = function() {
                  return resetTest();
                };
              };
            })(this)
          ]
        });
      };

      return AdminStart_Ctrl_Email;

    })(StartBase);
    return AdminStart_Ctrl_Email.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Email.js.map
