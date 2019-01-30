// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'Admin/TicketAccounts/FormModel/EditTicketAccountModel',
  'underscore'
], function(
  Admin_Ctrl_Base,
  EditTicketAccountModel,
  _
) {
  class Admin_TicketAccounts_Ctrl_Edit extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.getAccessToken = this.getAccessToken.bind(this);
      this.setCertificate = this.setCertificate.bind(this);
      this.setKey = this.setKey.bind(this);
      this.deleteCertificate = this.deleteCertificate.bind(this);
      this.deleteKey = this.deleteKey.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID = 'Admin_TicketAccounts_Ctrl_Edit';
      this.CTRL_AS = 'TicketAccountsEdit';
      this.DEPS    = ['Api', 'Growl', 'TicketAccountsData', '$stateParams', '$modal', 'dpObTypesDefTicketActions', '$location', '$upload', '$http', 'Api2'];
    }

    init() {
      this.actionsTypeDef = this.dpObTypesDefTicketActions;
      this.$scope.actionOptionTypes = [];
      this.$scope.actions_form = {};
      this.$scope.message_count = null;

      this.accountId = parseInt(this.$stateParams.id || 0);
      this.didPassTest = false;
      this.testMessageCount = 0;
      this.didConfirmExistingMessages = false;
      this.test_email = {
        to: window.DP_PERSON_EMAIL,
        from: '',
        subject: 'Test email',
        message: 'This is a test. If you see this email in your inbox, your outgoing email account settings are correct.'
      };
      this.$scope.path = this.$location.path();
      return this.deferredData = {};
    }

    updateCriteriaOptionTypes() {
      const types = ['web', 'web.user'];
      const setActionOptions = this.actionsTypeDef.getOptionsForTypes(types, { dynamicOptions: this.customActions });
      this.$scope.actionOptionTypes.length = 0;
      return Array.from(setActionOptions).map((opt) =>
        this.$scope.actionOptionTypes.push(opt));
    }

    getFormModel() {
      return new EditTicketAccountModel(this.account || {}, this.deps || [], this.trigger || {}, this.brands || {});
    }

    initialLoad() {
      const dep_promise = this.DataService.get('TicketDeps').loadList().then( list => {
        return this.deps = list;
      });

      const brands_promise = this.Api2.sendGet('brands').then( res => {
        return this.brands = res.data.data;
      });

      const get = {
        customActions: '/ticket_triggers/get-custom-actions'
      };
      if (this.accountId) {
        get.trigger = `/ticket_triggers/email_accounts/${this.accountId}`;
      } else {
        get.trigger = "/ticket_triggers/newticket";
      }

      const trigger_promise = this.Api.sendDataGet(get).then( result => {
        this.customActions = result.data.customActions.action_defs;

        if (result.data && result.data.trigger) {
          if (result.data.trigger.trigger) {
            this.trigger = result.data.trigger.trigger;
            this.triggerId = this.trigger.id;

            if (__guard__(this.trigger.actions != null ? this.trigger.actions.actions : undefined, x => x.length)) {
              this.$scope.actions_form = {};
              return (() => {
                const result1 = [];
                for (let action of Array.from(this.trigger.actions.actions)) {
                  const rowId = _.uniqueId('action');
                  result1.push(this.$scope.actions_form[rowId] = action);
                }
                return result1;
              })();
            }
          } else {
            this.trigger = result.data.trigger;
            return this.triggerId = 0;
          }
        } else {
          this.trigger = {};
          return this.triggerId = 0;
        }
      });

      const trigger_data_promise = this.actionsTypeDef.loadDataOptions();

      const proms = [trigger_promise, trigger_data_promise, dep_promise, brands_promise];

      if (!this.accountId) {
        this.account = {is_enabled: true, is_all_brands: true};
        this.trigger = {};
      } else {
        const data_promise = this.Api.sendDataGet({
          'email_account': `/email_accounts/${this.accountId}`
        }).then( result => {
          this.account = result.data.email_account.email_account;
          return this.trigger = result.data.email_account.trigger;
        });

        proms.push(data_promise);
      }

      const final_promise = this.$q.all(proms);

      final_promise.then(() => {
        this.form_model = this.getFormModel();
        if (this.deferredData.gmail != null) {
          for (let type of ['in', 'out']) {
            for (let v of ['clientId', 'clientSecret', 'token', 'refreshToken']) {
              this.form_model.form[type + '_gmail_account'][v] = this.deferredData.gmail[v];
            }
          }
        }

        this.$scope.form = this.form_model.form;

        if (!this.accountId) {
          this.$scope.form.incoming_type = '';
          this.$scope.form.outgoing_type = 'smtp';
        }

        if (!this.$scope.form.outgoing_type) {
          this.$scope.form.outgoing_type = 'php_mail';
        }

        this.updateCriteriaOptionTypes();

        if (this.$scope.form.encryption_enabled) {
          return this.$scope.show_adv = true;
        }
      });
      return final_promise;
    }


    /*
     * Saves the current form
     *
     * @return {promise}
     */
    saveAccount() {
      let is_new, promise;
      if (this.$scope.form_props.$invalid) { return; }

      if (!this.account.id && !this.new_is_confirmed && (this.$scope.form.account_type !== 'outgoing')) {
        this.showNewAccountConfirm();
        return;
      }

      let postData = this.form_model.getFormData();

      const triggerSaver = () => {
        postData = {
          actions:       []
        };
        if (this.$scope.actions_form) {
          for (let _x of Object.keys(this.$scope.actions_form || {})) {
            const act = this.$scope.actions_form[_x];
            if (act.type) {
              postData.actions.push(act);
            }
          }
        }
        return this.Api.sendPostJson(`/ticket_triggers/email_accounts/${this.account.id}`, postData);
      };

      this.startSpinner('saving_account');
      if (this.account.id) {
        is_new = false;
        promise = this.Api.sendPostJson(`/email_accounts/${this.account.id}`, postData);
      } else {
        is_new = true;
        promise = this.Api.sendPutJson('/email_accounts', postData);
      }

      promise.success( result => {
        this.account.id = result.email_account_id || this.account.id;
        this.account.is_enabled = this.$scope.form.is_enabled;
        return triggerSaver().then(() => {
          this.stopSpinner('saving_account', true).then(() => {
            return this.Growl.success(this.getRegisteredMessage('saved_account'));
          });
          this.form_model.apply();
          this.TicketAccountsData.updateModel(this.account);
          return this.uploadFiles().then(() => {
              this.skipDirtyState();
              if (is_new) {
                return this.$state.go('emails.ticket_accounts.gocreate');
              } else {
                return this.$state.go('emails.ticket_accounts');
              }
            }
            , err => {
              return this.Growl.error(err);
          });
        });
      });
      promise.error( (info, code) => {
        this.stopSpinner('saving_account', true);
        if (((info != null ? info.error_code : undefined) === 'invalid_data') && (info != null ? info.error_message : undefined)) {
          return this.showAlert(info.error_message);
        } else {
          return this.applyErrorResponseToView(info);
        }
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

      return this.Api.sendPostJson('/email_accounts/test-outgoing-account', form_data, null, { timeout: 12000});
    }


    /*
     * Show the test account modal
     */
    testAccountModal() {
      let inst;
      const me = this;
      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketAccounts/test-account-modal.html'),
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
              $scope.message_count = result.message_count;
              return me.$scope.message_count = result.message_count;
            }).error(() => {
              $scope.showing_log   = true;
              $scope.is_testing    = false;
              $scope.is_success    = false;
              $scope.log           = "Server Error";
              $scope.message_count = 0;
              return me.$scope.message_count = null;
            });
          };

          testNow();

          return $scope.testNow = () => testNow();
        }
        ]
      });
    }

    showNewAccountConfirm() {
      const me = this;
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketAccounts/new-account-confirm.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) => {

          $scope.message_count = me.$scope.message_count;

          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.confirm = () => $modalInstance.close(true);
        }
        ]
      });

      return inst.result.then( r => {
        if (r) {
          this.new_is_confirmed = true;
          return this.saveAccount();
        }
      });
    }

    setupTestModalScope($scope) {
    }

    /*
      * Show the test account modal
    */
    testOutgoingModal() {
      let inst;
      const me = this;
      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('TicketAccounts/test-outgoing-modal.html'),
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
          me.setupTestModalScope($scope);

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
            }).error( result => {
              if ((result.status === 0) || (result.status === 524)) {
                $scope.showing_log   = true;
                $scope.is_testing    = false;
                $scope.is_success    = false;
                $scope.log           = "The test failed due to a network problem. For example, the test may have timed out due to a firewall blocking it.";
                return $scope.message_count = 0;
              } else {
                $scope.showing_log   = true;
                $scope.is_testing    = false;
                $scope.is_success    = false;
                $scope.log           = "Server Error";
                return $scope.message_count = 0;
              }
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

    handleBrand(brandId, e) {
      const index = this.form_model.form.brands.indexOf(brandId);
      if (index === -1) {
        return this.form_model.form.brands.unshift(brandId);
      } else {
        if (this.form_model.form.brands.length > 1) {
          return this.form_model.form.brands.splice(index, 1);
        } else {
          alert("Account needs to be linked to at least one Brand");
          $(e.target).prop("checked", true);
          return true;
        }
      }
    }

    getCode(url) {
      const newWindow = window.open(url, 'name', 'height=600,width=450');
      if (window.focus) { return newWindow.focus(); }
    }



    getAccessToken(url, type) {
      if (!(this.$scope.form[`${type}_gmail_account`].code || '').length) { return; }
      url = url + '?code=' + encodeURIComponent(this.$scope.form[`${type}_gmail_account`].code);
      return this.$http({method: 'GET', url }).then(res => {
        if (res.data != null ? res.data.error : undefined) {
          return this.Growl.error(res.data.error);
        } else {
          this.$scope.form[`${type}_gmail_account`].token = res.data.access_token;
          return this.$scope.form[`${type}_gmail_account`].refreshToken = res.data.refresh_token;
        }
      });
    }


    onFileSelect(files, type) {
      if (!this.$scope.files) {
        this.$scope.files = {};
      }
      this.$scope.files[type] = files[0];
      if (type === 'certificate') {
        return this.$scope.form.cert_file = files[0].name;
      } else if (type === 'key') {
        return this.$scope.form.key_file = files[0].name;
      }
    }

    uploadFiles() {
      return new Promise( (resolve, reject) => {
        if (!this.$scope.files || (!this.$scope.files.certificate && !this.$scope.files.key)) {
          return resolve();
        }
        if (!this.$scope.files.certificate || !this.$scope.files.key) {
          return reject('You must add a certificate and a key');
        }
        return this.$upload.upload({
          url: this.Api2.formatUrl(`/email_accounts/${this.form_model.account.id}/encryption`),
          data:{ cert: this.$scope.files.certificate, key: this.$scope.files.key, pass_phrase: this.form_model.form.key_pass_phrase }
        }).success( data => {
          this.setCertificate(data.data.cert_blob);
          this.setKey(data.data.key_blob);
          return resolve();
        }).error( data => {
          return reject((data != null ? data.error_message : undefined) || 'Error');
        });
      });
    }

    setCertificate(blob) {
      if ((blob == null)) {
        return this.$scope.form.cert_file = null;
      } else {
        return this.$scope.form.cert_file = blob.filename;
      }
    }

    setKey(blob) {
      if ((blob == null)) {
        return this.$scope.form.key_file = null;
      } else {
        return this.$scope.form.key_file = blob.filename;
      }
    }

    deleteCertificate() {
      if (this.form_model.account.cert_blob) {
        return this.Api2.sendDelete(`/email_accounts/${this.form_model.account.id}/certificate`).success(() => {
          return this.$scope.form.cert_file = null;
        });
      } else {
        this.$scope.files.certificate = null;
        return this.$scope.form.cert_file = null;
      }
    }


    deleteKey() {
      if (this.form_model.account.cert_blob) {
        return this.Api2.sendDelete(`/email_accounts/${this.form_model.account.id}/key`).success(() => {
          return this.$scope.form.key_file = null;
        });
      } else {
        this.$scope.files.key = null;
        return this.$scope.form.key_file = null;
      }
    }
  }
  Admin_TicketAccounts_Ctrl_Edit.initClass();




  return Admin_TicketAccounts_Ctrl_Edit.EXPORT_CTRL();
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}