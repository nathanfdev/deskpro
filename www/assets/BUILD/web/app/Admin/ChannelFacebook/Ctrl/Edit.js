define([
  'Admin/Main/Ctrl/Base',
  'Admin/ChannelFacebook/FormModel/EditFacebookPageModel',
  'DeskPRO/Util/Util'
], (
  Admin_Ctrl_Base,
  Admin_ChannelFacebook_FormModel_EditFacebookPageModel,
  Util
) => {
  class Admin_ChannelFacebook_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ChannelFacebook_Ctrl_Edit';
      this.CTRL_AS = 'ChannelFacebookEdit';
      this.DEPS    = ['Api', 'Growl', 'FacebookPagesData', '$stateParams', '$state', '$timeout'];
    }

    init() {
      this.pageId = parseInt(this.$stateParams.id || 0);
      this.page = null;
      return this.form_model = null;
    }

    initialLoad() {
      const promise = this.Api.sendGet(`/channel/facebook/page/${this.pageId}`).then((result) => {
        if (result.data) {
          this.page = result.data;
          this.form_model = new Admin_ChannelFacebook_FormModel_EditFacebookPageModel(this.page || {});
          return this.setFormOnScope();
        }
      });

      return promise;
    }

    setFormOnScope() {
      return this.$scope.form = this.form_model.form;
    }

    savePage() {
      this.startSpinner('saving_page');
      const postData = { page: this.form_model.getFormData() };
      return this.Api.sendPostJson(`/channel/facebook/page/${this.pageId}`, postData).then((result) => {
        this.page = result.data;
        this.FacebookPagesData.updateModel(this.page);
        this.Growl.success(this.getRegisteredMessage('saved_page'));
        this.stopSpinner('saving_page');
        return this.$scope.$parent.ChannelFacebookList.pingElement('save_page');
      });
    }

    connect() {
      const postData = this.getPostData();
      const promise = this.Api.sendPostJson('/channel/facebook/connect_provider', postData);
      promise.then((result) => {
        if (result.data.success) {
          this.$scope.connection_problem = false;
          this.page = result.data.account;
          this.form_model.setAccountData(result.data.account);
          if (this.pageId) {
            this.FacebookPagesData.updateModel(this.page);
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
      const promise = this.Api.sendPostJson('/channel/facebook/setup-and-test/twilio', postData);
      promise.then((result) => {
        if (result) {
          var checkTestStatus = () => {
            const url = `/channel/facebook/page/${this.pageId}`;
            return this.$timeout(() => this.Api.sendGet(url).then((result) => {
              if (result.data.is_tested) {
                this.stopSpinner('sms_test_provider');
                this.page = result.data;
                this.form_model.setAccountData(result.data);
                this.FacebookPagesData.updateModel(this.page);
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
  }
  Admin_ChannelFacebook_Ctrl_Edit.initClass();


  return Admin_ChannelFacebook_Ctrl_Edit.EXPORT_CTRL();
});
