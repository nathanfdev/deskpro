/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util',
  'Admin/TicketAccounts/FormModel/EditTicketAccountModel',
], function(Util, BaseFormModel) {
  let Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel;
  return (Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel = class Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel extends BaseFormModel {
    constructor(account, deps, trigger, brands) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.account = account;
      super(this.account, deps, trigger, brands);

      if (this.form.address && (this.form.address.indexOf('@') !== -1)) {
        this.form.address_name = this.form.address.split('@')[0];
      }

      this.form.use_custom_email_address = false;

      if (this.account.other_addresses && this.account.other_addresses.length && this.account.options.custom_email_address) {
        this.form.custom_email_address = this.account.other_addresses.shift();
        this.form.use_custom_email_address = true;
      }

      if (this.account.other_addresses && this.account.other_addresses.length) {
        this.form.with_email_aliases = true;
        this.form.other_addresses = this.account.other_addresses.join(', ');
      } else {
        this.form.with_email_aliases = false;
        this.form.other_addresses = '';
      }
    }

    getFormData() {
      const form = super.getFormData();

      if (!form.with_email_aliases) {
        form.other_addresses = '';
      }

      return form;
    }

    apply() {
      this.account.address = this.form.address_name + '@' + window.DPC_SITE_DOMAIN;
      if ((this.account.options == null)) {
        this.account.options = {};
      }

      if (this.form.use_custom_email_address && this.form.custom_email_address) {
        return this.account.options.custom_email_address = this.form.custom_email_address;
      } else {
        return this.account.options.custom_email_address = null;
      }
    }
  });
});