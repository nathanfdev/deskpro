(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/ChannelSms/FormModel/EditSmsAccountModel'], function(Admin_Ctrl_Base, Admin_ChannelSms_FormModel_EditSmsAccountModel) {
    var Admin_ChannelSms_Ctrl_Edit;
    Admin_ChannelSms_Ctrl_Edit = (function(_super) {
      __extends(Admin_ChannelSms_Ctrl_Edit, _super);

      function Admin_ChannelSms_Ctrl_Edit() {
        return Admin_ChannelSms_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_ChannelSms_Ctrl_Edit.CTRL_ID = 'Admin_ChannelSms_Ctrl_Edit';

      Admin_ChannelSms_Ctrl_Edit.CTRL_AS = 'ChannelSmsEdit';

      Admin_ChannelSms_Ctrl_Edit.DEPS = ['Api', 'Growl', 'SmsAccountsData', '$stateParams', '$state'];

      Admin_ChannelSms_Ctrl_Edit.prototype.init = function() {
        return this.accountId = parseInt(this.$stateParams.id || 0);
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.getFormModel = function() {
        return new Admin_ChannelSms_FormModel_EditSmsAccountModel(this.account || {});
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        if (this.accountId) {
          promise = this.Api.sendGet("/channel/sms/account/" + this.accountId).then((function(_this) {
            return function(result) {
              if (result.data) {
                _this.account = result.data;
                _this.form_model = _this.getFormModel();
              }
              return _this.setFormOnScope();
            };
          })(this));
        } else {
          this.setFormOnScope();
        }
        return promise;
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.setFormOnScope = function() {
        if (!this.form_model) {
          this.form_model = this.getFormModel();
        }
        return this.$scope.form = this.form_model.form;
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.clearCredentials = function() {
        this.form_model.markConnected(false);
        return this.$scope.connection_problem = false;
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.getPostData = function() {
        return {
          account: this.form_model.getFormData()
        };
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.connect = function() {
        var postData, promise;
        postData = this.getPostData();
        promise = this.Api.sendPostJson("/channel/sms/connect_provider", postData);
        promise.then((function(_this) {
          return function(result) {
            if (result.data.success) {
              _this.$scope.connection_problem = false;
              _this.account = result.data.account;
              _this.form_model.setAccountData(result.data.account);
              if (_this.accountId) {
                _this.SmsAccountsData.updateModel(_this.account);
              }
              _this.ngApply();
              _this.Growl.success(_this.getRegisteredMessage('connected'));
            } else {
              _this.$scope.connection_problem = true;
              _this.form_model.markConnected(false);
              _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
            }
            return _this.stopSpinner('sms_connect_provider');
          };
        })(this));
        promise.error((function(_this) {
          return function(result) {
            _this.$scope.connection_problem = true;
            _this.form_model.markConnected(false);
            _this.stopSpinner('sms_connect_provider');
            return _this.Growl.error(_this.getRegisteredMessage('connected_fail'));
          };
        })(this));
        this.startSpinner('sms_connect_provider');
        return promise;
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.testRoundTrip = function() {
        var postData, promise;
        this.form_model.markTested(true);
        postData = this.getPostData();
        promise = this.Api.sendPostJson("/channel/sms/test_account", {
          account: postData
        });
        promise.then((function(_this) {
          return function(result) {
            if (result) {
              _this.ngApply();
            } else {
              _this.form_model.markTested(false);
            }
            return _this.stopSpinner('sms_test');
          };
        })(this));
        promise.error((function(_this) {
          return function(result) {
            _this.$scope.connection_problem = true;
            _this.form_model.markTested(true);
            return _this.stopSpinner('sms_test');
          };
        })(this));
        this.startSpinner('sms_test');
        return promise;
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.saveAccount = function() {
        var postData;
        postData = this.getPostData();
        if (this.accountId) {
          return this.Api.sendPostJson("/channel/sms/account/" + this.accountId, postData).then((function(_this) {
            return function(result) {
              _this.account = result.data.account;
              _this.SmsAccountsData.updateModel(_this.account);
              return _this.Growl.success(_this.getRegisteredMessage('saved_account'));
            };
          })(this));
        } else {
          return this.Api.sendPutJson("/channel/sms/account", postData).then((function(_this) {
            return function(result) {
              _this.account = result.data.account;
              _this.accountId = _this.account.id;
              _this.SmsAccountsData.addToList(_this.account);
              _this.Growl.success(_this.getRegisteredMessage('saved_account'));
              return _this.$state.go('tickets.channel_sms.edit', {
                id: _this.accountId
              });
            };
          })(this));
        }
      };

      return Admin_ChannelSms_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_ChannelSms_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
