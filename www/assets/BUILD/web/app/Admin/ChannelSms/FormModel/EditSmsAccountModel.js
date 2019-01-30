// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util'
], function(Util) {
  let Admin_ChannelSms_FormModel_EditSmsAccountModel;
  return (Admin_ChannelSms_FormModel_EditSmsAccountModel = class Admin_ChannelSms_FormModel_EditSmsAccountModel {
    constructor(account) {
      this.account = account;
      this.form = {account: {}};
      this.form.account.id = this.account.id || 0;
      this.form.account.type = this.account.type || "twilio";
      this.form.account.identifier = this.account.identifier || "";
      this.form.account.phone_number = this.account.phone_number || "";
      this.form.account.params = this.account.params || {};
      this.form.account.is_enabled = Util.isEmpty(this.account.is_enabled) ? false : this.account.is_enabled;
      this.form.account.is_connected = Util.isEmpty(this.account.is_connected) ? false : this.account.is_connected;
      this.form.account.is_tested = Util.isEmpty(this.account.is_tested) ? false : this.account.is_tested;
    }

    setAccountData(data) {
      this.form.account = data;
      if (Util.isEmpty(data.phone_number) && !Util.isEmpty(data.params != null ? data.params.numbers : undefined)) {
        return data.phone_number = data.params.numbers[0].number;
      }
    }

    getFormData() {
      const form = Util.clone(this.form, true);
      return form.account;
    }

    clearCredentials() {
      return this.markConnected(false);
    }

    markConnected(isConnected) {
      this.form.account.is_connected = isConnected;
      if (!isConnected) {
        this.markTested(false);
        this.form.account.identifier = null;
        return this.form.account.is_enabled = false;
      }
    }

    markTested(isTested) {
      this.form.account.is_tested = isTested;
      if (!isTested) {
        return this.form.account.is_enabled = false;
      }
    }

    setFriendlyName(name) {
      return this.form.account.identifier = name;
    }
  });
});
