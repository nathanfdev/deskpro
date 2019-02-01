define([
  'Admin/Main/Ctrl/Base',
  'Admin/ChannelSms/FormModel/EditSmsAccountModel'
], (
  Admin_Ctrl_Base,
  Admin_ChannelSms_FormModel_EditSmsAccountModel
) => {
  class Admin_ChannelSms_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ChannelSms_Ctrl_Edit';
      this.CTRL_AS = 'ChannelSmsEdit';
      this.DEPS    = ['Api', 'Growl', 'SmsAccountsData', '$stateParams', '$state', '$timeout'];
    }

    init() {
      return this.accountId = parseInt(this.$stateParams.id || 0);
    }

    getFormModel() {
      return new Admin_ChannelSms_FormModel_EditSmsAccountModel(this.account || {});
    }

    initialLoad() {
      let promise;
      if (this.accountId) {
        promise = this.Api.sendGet(`/channel/sms/account/${this.accountId}`).then((result) => {
          if (result.data) {
            this.account = result.data;
            this.form_model = this.getFormModel();
          }
          return this.setFormOnScope();
        });
      } else {
        this.setFormOnScope();
      }
      return promise;
    }

    setFormOnScope() {
      if (!this.form_model) {
        this.form_model = this.getFormModel();
      }
      return this.$scope.form = this.form_model.form;
    }

    clearCredentials() {
      this.form_model.markConnected(false);
      return this.$scope.connection_problem = false;
    }

    getPostData() {
      return { account: this.form_model.getFormData() };
    }

    connect() {
      const postData = this.getPostData();
      const promise = this.Api.sendPostJson('/channel/sms/connect_provider', postData);
      promise.then((result) => {
        if (result.data.success) {
          this.$scope.connection_problem = false;
          this.account = result.data.account;
          this.form_model.setAccountData(result.data.account);
          if (this.accountId) {
            this.SmsAccountsData.updateModel(this.account);
          }
          this.ngApply();
          this.Growl.success(this.getRegisteredMessage('connected'));
        } else {
          this.$scope.connection_problem = true;
          this.form_model.markConnected(false);
          this.Growl.error(this.getRegisteredMessage('connected_fail'));
        }
        return this.stopSpinner('sms_connect_provider');
      });
      promise.error((result) => {
        this.$scope.connection_problem = true;
        this.form_model.markConnected(false);
        this.stopSpinner('sms_connect_provider');
        return this.Growl.error(this.getRegisteredMessage('connected_fail'));
      });
      this.startSpinner('sms_connect_provider');
      return promise;
    }

    setupAndTest() {
      const postData = this.getPostData();
      const promise = this.Api.sendPostJson('/channel/sms/setup-and-test/twilio', postData);
      promise.then((result) => {
        if (result) {
          var checkTestStatus = () => {
            const url = `/channel/sms/account/${this.accountId}`;
            return this.$timeout(() => this.Api.sendGet(url).then((result) => {
              if (result.data.is_tested) {
                this.stopSpinner('sms_test_provider');
                this.account = result.data;
                this.form_model.setAccountData(result.data);
                this.SmsAccountsData.updateModel(this.account);
                this.ngApply();
                return this.Growl.success(this.getRegisteredMessage('setup_and_tested_success'));
              }
              return checkTestStatus();
            })
            , 1000);
          };
          return checkTestStatus();
        }
        this.Growl.error(this.getRegisteredMessage('connected_fail'));
        return this.form_model.markTested(false);
      });
      promise.error((result) => {
        this.$scope.connection_problem = true;
        this.Growl.error(this.getRegisteredMessage('connected_fail'));
        return this.stopSpinner('sms_test_provider');
      });
      this.startSpinner('sms_test_provider');
      return promise;
    }

    saveAccount() {
      const postData = this.getPostData();
      if (this.accountId) {
        return this.Api.sendPostJson(`/channel/sms/account/${this.accountId}`, postData).then((result) => {
          this.account = result.data.account;
          this.SmsAccountsData.updateModel(this.account);
          return this.Growl.success(this.getRegisteredMessage('saved_account'));
        });
      }
      return this.Api.sendPutJson('/channel/sms/account', postData).then((result) => {
        this.account = result.data.account;
        this.accountId = this.account.id;
        this.SmsAccountsData.addToList(this.account);
        this.Growl.success(this.getRegisteredMessage('saved_account'));
        return this.$state.go('tickets.channel_sms.edit', { id: this.accountId });
      });
    }
  }
  Admin_ChannelSms_Ctrl_Edit.initClass();


  return Admin_ChannelSms_Ctrl_Edit.EXPORT_CTRL();
});
