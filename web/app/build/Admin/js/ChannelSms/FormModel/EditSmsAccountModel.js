(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var Admin_ChannelSms_FormModel_EditSmsAccountModel;
    return Admin_ChannelSms_FormModel_EditSmsAccountModel = (function() {
      function Admin_ChannelSms_FormModel_EditSmsAccountModel(account) {
        var _ref;
        this.account = account;
        this.form = {
          account: {}
        };
        this.form.account.id = this.account.id || 0;
        this.form.account.type = this.account.type || "twilio";
        this.form.account.params = this.account.params || {
          sid: 'AC82e05c22887c7a336941107979bc9709',
          auth_token: '9755c4ee6ab8e43c7fca39a38f664a6e'
        };
        this.form.account.is_enabled = this.account.is_enabled;
        this.form.account.is_connected = this.account.is_connected;
        this.form.account.is_tested = this.account.is_enabled;
        this.form.numbers = ((_ref = this.account.phone_number) != null ? _ref.length : void 0) ? [
          {
            number: this.account.phone_number,
            display_name: this.account.phone_number
          }
        ] : [];
        this.form.account.phone_number = this.account.phone_number || null;
        this.form.account.identifier = this.account.identifier || "";
      }

      Admin_ChannelSms_FormModel_EditSmsAccountModel.prototype.getFormData = function() {
        var form;
        form = Util.clone(this.form, true);
        return form;
      };

      Admin_ChannelSms_FormModel_EditSmsAccountModel.prototype.getConnectData = function() {
        var form;
        form = Util.clone(this.form, true);
        return {
          type: form.account.type,
          params: form.account.params
        };
      };

      Admin_ChannelSms_FormModel_EditSmsAccountModel.prototype.clearCredentials = function() {
        return this.markConnected(false);
      };

      Admin_ChannelSms_FormModel_EditSmsAccountModel.prototype.markConnected = function(isConnected) {
        this.form.account.is_connected = isConnected;
        this.form.account.is_tested = false;
        if (!isConnected) {
          this.setNumbers([]);
          return this.form.account.identifier = null;
        }
      };

      Admin_ChannelSms_FormModel_EditSmsAccountModel.prototype.setNumbers = function(numbers) {
        var _ref;
        if (!((_ref = this.form.account.phone_number) != null ? _ref.length : void 0)) {
          this.form.account.phone_number = numbers[0].phone_number;
        }
        return this.form.numbers = numbers;
      };

      Admin_ChannelSms_FormModel_EditSmsAccountModel.prototype.setFriendlyName = function(name) {
        return this.form.account.identifier = name;
      };

      return Admin_ChannelSms_FormModel_EditSmsAccountModel;

    })();
  });

}).call(this);

//# sourceMappingURL=EditSmsAccountModel.js.map
