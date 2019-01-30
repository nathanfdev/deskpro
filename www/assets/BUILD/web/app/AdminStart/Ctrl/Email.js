/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['AdminStart/Ctrl/StartBase', 'Admin/TicketAccounts/FormModel/EditTicketAccountModel'], function(StartBase, EditTicketAccountModel) {
  class AdminStart_Ctrl_Email extends StartBase {
    static initClass() {
      this.CTRL_ID = 'AdminStart_Ctrl_Email';
      this.DEPS    = ['$modal', '$location'];
    }

    init() {
      this.$scope.TicketAccountsEdit = this;
      this.test_email = {
        to: '',
        from: '',
        subject: 'Test email',
        message: 'This is a test. If you see this email in your inbox, your outgoing email account settings are correct.'
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
      this.form_model.form.incoming_type = '';
      this.form_model.form.outgoing_type = 'php_mail';
      return this.$scope.form = this.form_model.form;
    }

    /*
     * Saves the current form
     *
     * @return {promise}
     */
    saveAndContinue() {
      this.$scope.email_is_error = null;
      const postData = this.form_model.getFormData();
      postData.is_enabled = true;

      if (!postData.address || (postData.address.length < 3) || (postData.address.indexOf('@') === -1)) {
        this.$scope.email_is_error = 'invalid_email';
        return;
      }

      this.$scope.is_loading = true;
      const promise = this.Api.sendPostJson('/email_accounts', postData);

      promise.success( result => {
        this.account.id = result.email_account_id || this.account.id;
        this.account.is_enabled = true;
        return this.$location.path('/finish');
      });
      promise.error( (info, code) => {
        this.$scope.is_loading = false;
        return this.$scope.email_is_error = 'general';
      });

      return promise;
    }


    /*
      * Test current account settings
      *
      * @return {promise}
    */
    loadAccountTest() {
      return this.Api.sendPostJson('/email_accounts/test-account', this.form_model.getFormData()).success( result => {
        return this.didPassTest = result.is_success;
      });
    }


    /*
      * Test current outgoing settings with message details from @test_email object.
      *
      * @return {promise}
    */
    loadOutgoingAccountTest() {
      const form_data = this.form_model.getFormData();
      form_data.test_email = this.test_email;

      return this.Api.sendPostJson('/email_accounts/test-outgoing-account', form_data);
    }


    /*
      * Show the test account modal
    */
    testAccountModal() {
      let inst;
      return inst = this.$modal.open({
        templateUrl: 'AdminInterface/TicketAccounts/test-account-modal.html',
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) => {
          $scope.dismiss = () => {
            return $modalInstance.dismiss();
          };

          $scope.showLog = () => {
            return $scope.showing_log = true;
          };

          const testNow = () => {
            $scope.showing_log = false;
            $scope.is_testing = true;
            return this.loadAccountTest().success( result => {
              $scope.is_testing    = false;
              $scope.is_success    = result.is_success;
              $scope.log           = result.log;
              return $scope.message_count = result.message_count;
            }).error(() => {
              $scope.showing_log   = true;
              $scope.is_testing    = false;
              $scope.is_success    = false;
              $scope.log           = "Server Error";
              return $scope.message_count = 0;
            });
          };

          testNow();

          return $scope.testNow = () => testNow();
        }
        ]
      });
    }


    /*
      * Show the test account modal
    */
    testOutgoingModal() {
      let inst;
      const me = this;
      return inst = this.$modal.open({
        templateUrl: 'AdminInterface/TicketAccounts/test-outgoing-modal.html',
        resolve: {
          test_email: () => {
            this.test_email.from = this.form_model.form.address;
            return this.test_email;
          }
        },
        controller: ['$scope', '$modalInstance', 'test_email', ($scope, $modalInstance, test_email) => {
          $scope.dismiss = () => {
            return $modalInstance.dismiss();
          };

          $scope.showLog = () => {
            return $scope.showing_log = true;
          };

          $scope.test_email = test_email;

          const testNow = () => {
            $scope.testing_started = true;
            $scope.showing_log = false;
            $scope.is_testing = true;

            if (!test_email.from) {
              test_email.from = me.form_model.form.address;
            }

            return me.loadOutgoingAccountTest().success( result => {
              $scope.is_testing    = false;
              $scope.is_success    = result.is_success;
              $scope.log           = result.log;
              return $scope.message_count = result.message_count;
            }).error(() => {
              $scope.showing_log   = true;
              $scope.is_testing    = false;
              $scope.is_success    = false;
              $scope.log           = "Server Error";
              return $scope.message_count = 0;
            });
          };

          const resetTest = () => {
            return $scope.testing_started = false;
          };

          $scope.testNow = () => testNow();
          return $scope.resetTest = () => resetTest();
        }
        ]
      });
    }
  }
  AdminStart_Ctrl_Email.initClass();

  return AdminStart_Ctrl_Email.EXPORT_CTRL();
});