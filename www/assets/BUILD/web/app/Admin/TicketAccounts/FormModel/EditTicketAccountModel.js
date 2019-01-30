// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util'
], function(Util) {
  let Admin_TicketAccounts_FormModel_EditTicketAccountModel;
  return (Admin_TicketAccounts_FormModel_EditTicketAccountModel = class Admin_TicketAccounts_FormModel_EditTicketAccountModel {
    constructor(account, deps, trigger, brands) {
      this.account = account;
      this.form = {};
      this.form.account_type        = this.account.account_type || 'tickets';
      this.form.address             = this.account.address;
      this.form.is_enabled          = this.account.is_enabled;
      this.form.incoming_type       = 'pop3';
      this.form.in_gmail_account    = {
        mode: "read",
        read_mailbox_type: "inbox",
        type: 'pop3'
      };
      this.form.in_pop3_account     = {};
      this.form.in_imap_account     = {};
      this.form.in_exchange_account = {};
      this.form.in_office365_account  = {};

      this.form.outgoing_type     = 'php_mail';
      this.form.out_gmail_account =
        {type: 'password'};
      this.form.out_smtp_account  = {};
      this.form.out_exchange_account  = {};
      this.form.out_office365_account  = {};

      this.form.in_pop3_account.secure_mode = "ssl";
      this.form.in_imap_account.secure_mode = "ssl";
      this.form.in_imap_account.mode = "read";
      this.form.in_imap_account.read_mailbox_type = "inbox";
      this.form.in_exchange_account.mode = "read";
      this.form.in_exchange_account.read_mailbox_type = "inbox";
      this.form.out_smtp_account.secure_mode = "ssl";
      this.form.in_pop3_account.port = 110;
      this.form.out_smtp_account.port = 25;

      if (this.account.other_addresses && this.account.other_addresses.length) {
        this.form.with_email_aliases = true;
        this.form.other_addresses = this.account.other_addresses.join(', ');
      } else {
        this.form.with_email_aliases = false;
        this.form.other_addresses = '';
      }

      this.form.encryption_enabled = ((this.account.cert_blob != null) || (this.account.key_blob != null));
      this.form.cert_file = this.account.cert_blob != null ? this.account.cert_blob.filename : undefined;
      this.form.key_file = this.account.key_blob != null ? this.account.key_blob.filename : undefined;
      this.form.key_pass_phrase = this.account.key_pass_phrase;

      //--------------------
      // Brands
      //--------------------
      if (brands != null ? brands.length : undefined) {
        this.form.is_all_brands = this.account.is_all_brands;
        this.form.brands = this.account.brand_ids && this.account.brand_ids.length ? this.account.brand_ids : brands.map(x => x.id);
      } else {
        this.form.is_all_brands = true;
        this.form.brands        = [];
      }

      //--------------------
      // Trigger
      //--------------------

      this.form.trigger_actions = {
        SetDepartment: {
          options: {
            department_id: '0'
          }
        },
        SendUserNewEmail: {
          enabled: false,
          options: {}
        }
      };

      if (deps && deps.length) {
        this.form.trigger_actions.SetDepartment.options.department_id = deps[0].id+'';
      }

      if (trigger && __guard__(trigger.actions != null ? trigger.actions.actions : undefined, x => x.length)) {
        for (let act of Array.from(trigger.actions.actions)) {
          if (act.type === 'SetDepartment') {
            this.form.trigger_actions.SetDepartment.options = act.options;
          } else if (act.type === 'SendUserNewEmail') {
            this.form.trigger_actions.SendUserNewEmail.enabled = true;
            this.form.trigger_actions.SendUserNewEmail.options = act.options;

            if (['helpdesk_name', 'site_name', 'performer'].indexOf(this.form.trigger_actions.SendUserNewEmail.options.from_name) === -1) {
              this.form.trigger_actions.SendUserNewEmail.options.from_name_custom = this.form.trigger_actions.SendUserNewEmail.options.from_name;
              this.form.trigger_actions.SendUserNewEmail.options.from_name = 'custom';
            }
          }
        }
      }

      if (!this.form.trigger_actions.SendUserNewEmail.enabled) {
        this.form.trigger_actions.SendUserNewEmail.options = {
          template: 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
          from_name: 'helpdesk_name'
        };
      }

      //--------------------
      // Init in account
      //--------------------

      if (this.account.incoming_account_type) {
        this.form.incoming_type = this.account.incoming_account_type;

        if (this.form.incoming_type === 'pop3') {
          this.form.in_pop3_account.host        = this.account.incoming_account.host;
          this.form.in_pop3_account.port        = this.account.incoming_account.port;
          this.form.in_pop3_account.secure_mode = this.account.incoming_account.secure_mode;
          this.form.in_pop3_account.user        = this.account.incoming_account.user;
          this.form.in_pop3_account.password    = this.account.incoming_account.password;

          this.form.in_pop3_account.disable_cert_validation = this.account.incoming_account.disable_cert_validation;
          if (this.form.in_pop3_account.secure_mode && (this.form.in_pop3_account.secure_mode !== '')) {
            this.form.in_pop3_account.secure = true;
          }
        }

        if (this.form.incoming_type === 'imap') {
          this.form.in_imap_account.host        = this.account.incoming_account.host;
          this.form.in_imap_account.port        = this.account.incoming_account.port;
          this.form.in_imap_account.secure_mode = this.account.incoming_account.secure_mode;
          this.form.in_imap_account.no_validation = this.account.incoming_account.no_validation;
          this.form.in_imap_account.user        = this.account.incoming_account.user;
          this.form.in_imap_account.password    = this.account.incoming_account.password;
          this.form.in_imap_account.mode        = this.account.incoming_account.mode || 'read';
          if (this.form.in_imap_account.secure_mode && (this.form.in_imap_account.secure_mode !== '')) {
            this.form.in_imap_account.secure = true;
          }
          if (this.account.incoming_account.read_mailbox && (this.account.incoming_account.read_mailbox !== '')) {
            this.form.in_imap_account.read_mailbox = this.account.incoming_account.read_mailbox;
            this.form.in_imap_account.read_mailbox_type = 'folder';
          }
          if (this.account.incoming_account.mode === 'archive') {
            this.form.in_imap_account.archive_mailbox = this.account.incoming_account.archive_mailbox;
          }
        }

        if (this.form.incoming_type === 'exchange') {
          this.form.in_exchange_account.host        = this.account.incoming_account.host;
          this.form.in_exchange_account.user        = this.account.incoming_account.user;
          this.form.in_exchange_account.password    = this.account.incoming_account.password;
          this.form.in_exchange_account.mode        = this.account.incoming_account.mode || 'read';
          if (this.account.incoming_account.read_mailbox && (this.account.incoming_account.read_mailbox !== '')) {
            this.form.in_exchange_account.read_mailbox = this.account.incoming_account.read_mailbox;
            this.form.in_exchange_account.read_mailbox_type = 'folder';
          }
          if (this.account.incoming_account.mode === 'archive') {
            this.form.in_exchange_account.archive_mailbox = this.account.incoming_account.archive_mailbox;
          }
        }

        if (this.form.incoming_type === 'gmail') {
          this.form.in_gmail_account.password     = this.account.incoming_account.password;
          this.form.in_gmail_account.token        = this.account.incoming_account.token;
          this.form.in_gmail_account.refreshToken = this.account.incoming_account.refreshToken;
          this.form.in_gmail_account.type         = this.account.incoming_account.type || 'pop3';
          this.form.in_gmail_account.mode        = this.account.incoming_account.mode || 'read';
          if (this.form.in_gmail_account.secure_mode && (this.form.in_gmail_account.secure_mode !== '')) {
            this.form.in_gmail_account.secure = true;
          }
          if (this.account.incoming_account.read_mailbox && (this.account.incoming_account.read_mailbox !== '')) {
            this.form.in_gmail_account.read_mailbox = this.account.incoming_account.read_mailbox;
            this.form.in_gmail_account.read_mailbox_type = 'folder';
          }
          if (this.account.incoming_account.mode === 'archive') {
            this.form.in_gmail_account.archive_mailbox = this.account.incoming_account.archive_mailbox;
          }
        }

        if (this.form.incoming_type === 'office365') {
          this.form.in_office365_account.password = this.account.incoming_account.password;
        }
      }

      //--------------------
      // Init out account
      //--------------------

      if (this.account.outgoing_account_type) {
        this.form.outgoing_type = this.account.outgoing_account_type;

        if (this.form.outgoing_type === 'smtp') {
          this.form.out_smtp_account.host        = this.account.outgoing_account.host;
          this.form.out_smtp_account.port        = this.account.outgoing_account.port;
          this.form.out_smtp_account.secure_mode = this.account.outgoing_account.secure_mode;
          this.form.out_smtp_account.user        = this.account.outgoing_account.user;
          this.form.out_smtp_account.password    = this.account.outgoing_account.password;
          if (this.form.out_smtp_account.secure_mode && (this.form.out_smtp_account.secure_mode !== '')) {
            this.form.out_smtp_account.disable_cert_validation = this.account.outgoing_account.disable_cert_validation;
            this.form.out_smtp_account.secure = true;
          }
        }

        if (this.form.outgoing_type === 'gmail') {
          this.form.out_gmail_account.password     = this.account.outgoing_account.password;
          this.form.out_gmail_account.token        = this.account.outgoing_account.token;
          this.form.out_gmail_account.refreshToken = this.account.outgoing_account.refreshToken;
          this.form.out_gmail_account.type         = this.account.outgoing_account.type || 'pop3';
        }

        if (this.form.outgoing_type === 'office365') {
          this.form.out_office365_account.password = this.account.outgoing_account.password;
        }

        if (this.form.outgoing_type === 'exchange') {
          this.form.out_exchange_account.host        = this.account.outgoing_account.host;
          this.form.out_exchange_account.user        = this.account.outgoing_account.user;
          this.form.out_exchange_account.password    = this.account.outgoing_account.password;
        }
      }
    }



    getFormData() {

      const form = Util.clone(this.form, true);

      if (!form.with_email_aliases) {
        form.other_addresses = '';
      }

      if (form.incoming_type === 'gmail') {
        form.in_gmail_account.user = form.address;
        if ((form.in_gmail_account.read_mailbox_type === 'inbox') || (form.in_gmail_account.read_mailbox === '')) {
          form.in_gmail_account.read_mailbox = null;
        }
        if (form.in_gmail_account.mode === 'archive') {
          if (!this.form.in_gmail_account.archive_mailbox || (this.form.in_gmail_account.archive_mailbox === '')) {
            form.in_gmail_account.mode = 'read';
            form.in_gmail_account.archive_mailbox = '';
          }
        }
      }
      if (form.outgoing_type === 'gmail') {
        form.out_gmail_account.user = form.address;
      }

      if (form.incoming_type === 'office365') {
        form.in_office365_account.user = form.address;
      }
      if (form.outgoing_type === 'office365') {
        form.out_office365_account.user = form.address;
      }

      if (form.incoming_type === 'imap') {
        if ((form.in_imap_account.read_mailbox_type === 'inbox') || (form.in_imap_account.read_mailbox === '')) {
          form.in_imap_account.read_mailbox = null;
        }
        if (form.in_imap_account.mode === 'archive') {
          if (!this.form.in_imap_account.archive_mailbox || (this.form.in_imap_account.archive_mailbox === '')) {
            form.in_imap_account.mode = 'read';
            form.in_imap_account.archive_mailbox = '';
          }
        }
      }

      if (form.incoming_type === 'exchange') {
        if ((form.in_exchange_account.read_mailbox_type === 'inbox') || (form.in_exchange_account.read_mailbox === '')) {
          form.in_exchange_account.read_mailbox = null;
        }
        if (form.in_exchange_account.mode === 'archive') {
          if (!this.form.in_exchange_account.archive_mailbox || (this.form.in_exchange_account.archive_mailbox === '')) {
            form.in_exchange_account.mode = 'read';
            form.in_exchange_account.archive_mailbox = '';
          }
        }
      }


      if (form.incoming_type === 'pop3') {
        if (form.in_pop3_account.secure) {
          form.in_pop3_account.secure_mode = form.in_pop3_account.secure_mode || 'ssl';
        } else {
          form.in_pop3_account.secure_mode = null;
        }
      }
      if (form.incoming_type === 'imap') {
        if (form.in_imap_account.secure) {
          form.in_imap_account.secure_mode = form.in_imap_account.secure_mode || 'ssl';
          form.in_imap_account.no_validation = form.in_imap_account.no_validation || false;
        } else {
          form.in_imap_account.secure_mode = null;
        }
      }
      if (form.outgoing_type === 'smtp') {
        if (form.out_smtp_account.secure) {
          form.out_smtp_account.secure_mode = form.out_smtp_account.secure_mode || 'ssl';
        } else {
          form.out_smtp_account.secure_mode = null;
        }
      }

      if (form.is_all_brands) {
        form.brands = [];
      }

      const trigger_actions = [];
      const department_id = parseInt(__guard__(this.form.trigger_actions.SetDepartment != null ? this.form.trigger_actions.SetDepartment.options : undefined, x => x.department_id) || 0);
      if (department_id) {
        trigger_actions.push({
          type: 'SetDepartment',
          options: {
            department_id
          }
        });
      }
      if (this.form.trigger_actions.SendUserNewEmail != null ? this.form.trigger_actions.SendUserNewEmail.enabled : undefined) {
        const { options } = this.form.trigger_actions.SendUserNewEmail;

        trigger_actions.push({
          type: 'SendUserNewEmail',
          options: {
            template:  options.template || 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
            from_name: options.from_name === 'custom' ? (options.from_name_custom || '') : (options.from_name || ''),
            do_cc_users: true,
            from_account: 0
          }
        });
      }

      form.trigger_actions = trigger_actions;

      return form;
    }

    apply() {
      return this.account.address = this.form.address;
    }
  });
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}