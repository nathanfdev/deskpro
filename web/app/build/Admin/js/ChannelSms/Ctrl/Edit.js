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

      Admin_ChannelSms_Ctrl_Edit.prototype.connect = function() {
        var connectUrl, postData, promise;
        postData = this.form_model.getConnectData();
        connectUrl = "/channel/sms/connect_provider";
        promise = this.Api.sendPostJson(connectUrl, postData);
        promise.then((function(_this) {
          return function(result) {
            if (result.data.success) {
              _this.$scope.connection_problem = false;
              _this.form_model.markConnected(true);
              _this.form_model.setNumbers(result.data.numbers);
              _this.form_model.setFriendlyName(result.data.friendly_name);
              _this.ngApply();
            } else {
              _this.$scope.connection_problem = true;
              _this.form_model.markConnected(false);
            }
            return _this.stopSpinner('sms_connect_provider');
          };
        })(this));
        promise.error((function(_this) {
          return function(result) {
            _this.$scope.connection_problem = true;
            return _this.form_model.markConnected(false);
          };
        })(this));
        this.startSpinner('sms_connect_provider');
        return promise;
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.testRoundTrip = function() {
        return alert("testing");
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.saveAccount = function() {
        var formData;
        formData = this.form_model.getFormData().account;
        if (this.accountId) {
          return this.Api.sendPostJson("/channel/sms/account/" + this.accountId, formData).then((function(_this) {
            return function(result) {
              _this.account = formData;
              _this.Growl.success(_this.getRegisteredMessage('saved_account'));
              return _this.SmsAccountsData.updateModel(_this.account);
            };
          })(this));
        } else {
          return this.Api.sendPutJson("/channel/sms/account", formData).then((function(_this) {
            return function(result) {
              _this.accountId = result.data.sms_account_id;
              _this.account = formData;
              _this.account.id = _this.accountId;
              _this.account.phone_number_region = result.data.phone_number_region;
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
