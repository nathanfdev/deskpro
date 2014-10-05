(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var Admin_ChannelFacebook_FormModel_EditFacebookPageModel;
    return Admin_ChannelFacebook_FormModel_EditFacebookPageModel = (function() {
      function Admin_ChannelFacebook_FormModel_EditFacebookPageModel(account) {
        this.account = account;
        this.form = {
          account: {}
        };
        this.form.account.id = this.account.id || 0;
        this.form.account.type = this.account.type || "twilio";
        this.form.account.identifier = this.account.identifier || "";
        this.form.account.phone_number = this.account.phone_number || "";
        this.form.account.params = this.account.params || {};
        this.form.account.is_enabled = Util.isEmpty(this.account.is_enabled) ? false : this.account.is_enabled;
        this.form.account.is_connected = Util.isEmpty(this.account.is_connected) ? false : this.account.is_connected;
        this.form.account.is_tested = Util.isEmpty(this.account.is_tested) ? false : this.account.is_tested;
      }

      Admin_ChannelFacebook_FormModel_EditFacebookPageModel.prototype.setAccountData = function(data) {
        var _ref;
        this.form.account = data;
        if (Util.isEmpty(data.phone_number) && !Util.isEmpty((_ref = data.params) != null ? _ref.numbers : void 0)) {
          return data.phone_number = data.params.numbers[0].number;
        }
      };

      Admin_ChannelFacebook_FormModel_EditFacebookPageModel.prototype.getFormData = function() {
        var form;
        form = Util.clone(this.form, true);
        return form.account;
      };

      Admin_ChannelFacebook_FormModel_EditFacebookPageModel.prototype.clearCredentials = function() {
        return this.markConnected(false);
      };

      Admin_ChannelFacebook_FormModel_EditFacebookPageModel.prototype.markConnected = function(isConnected) {
        this.form.account.is_connected = isConnected;
        if (!isConnected) {
          this.markTested(false);
          this.form.account.identifier = null;
          return this.form.account.is_enabled = false;
        }
      };

      Admin_ChannelFacebook_FormModel_EditFacebookPageModel.prototype.markTested = function(isTested) {
        this.form.account.is_tested = isTested;
        if (!isTested) {
          return this.form.account.is_enabled = false;
        }
      };

      Admin_ChannelFacebook_FormModel_EditFacebookPageModel.prototype.setFriendlyName = function(name) {
        return this.form.account.identifier = name;
      };

      return Admin_ChannelFacebook_FormModel_EditFacebookPageModel;

    })();
  });

}).call(this);

//# sourceMappingURL=EditFacebookPageModel.js.map
