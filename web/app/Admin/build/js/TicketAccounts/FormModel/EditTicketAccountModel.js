(function() {
  define(['DeskPRO/Util/Util'], function(Util) {
    var Admin_TicketAccounts_Form_EditTicketAccountModel;
    return Admin_TicketAccounts_Form_EditTicketAccountModel = (function() {
      function Admin_TicketAccounts_Form_EditTicketAccountModel(account, deps, trigger) {
        var act, _i, _len, _ref, _ref1, _ref2;
        this.account = account;
        this.form = {};
        this.form.account_type = this.account.account_type || 'tickets';
        this.form.address = this.account.address;
        this.form.incoming_type = 'pop3';
        this.form.in_gmail_account = {};
        this.form.in_pop3_account = {};
        this.form.in_imap_account = {};
        this.form.in_exchange_account = {};
        this.form.outgoing_type = 'mail';
        this.form.out_gmail_account = {};
        this.form.out_smtp_account = {};
        this.form.in_pop3_account.secure_mode = "ssl";
        this.form.in_imap_account.secure_mode = "ssl";
        this.form.in_imap_account.mode = "read";
        this.form.in_imap_account.read_mailbox_type = "inbox";
        this.form.in_exchange_account.mode = "read";
        this.form.in_exchange_account.read_mailbox_type = "inbox";
        this.form.out_smtp_account.secure_mode = "ssl";
        if (this.account.other_addresses && this.account.other_addresses.length) {
          this.form.with_email_aliases = true;
          this.form.other_addresses = this.account.other_addresses.join(', ');
        } else {
          this.form.with_email_aliases = false;
          this.form.other_addresses = '';
        }
        this.form.trigger_actions = {
          SetDepartment: {
            options: {
              department_id: '0'
            }
          },
          SendUserEmail: {
            enabled: false,
            options: {}
          }
        };
        if (deps && deps.length) {
          this.form.trigger_actions.SetDepartment.options.department_id = deps[0].id + '';
        }
        if (trigger && ((_ref = trigger.actions) != null ? (_ref1 = _ref.actions) != null ? _ref1.length : void 0 : void 0)) {
          _ref2 = trigger.actions.actions;
          for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
            act = _ref2[_i];
            if (act.type === 'SetDepartment') {
              this.form.trigger_actions.SetDepartment.options = act.options;
            } else if (act.type === 'SendUserEmail') {
              this.form.trigger_actions.SendUserEmail.enabled = true;
              this.form.trigger_actions.SendUserEmail.options = act.options;
              if (['helpdesk_name', 'site_name'].indexOf(this.form.trigger_actions.SendUserEmail.options.from_name) === -1) {
                this.form.trigger_actions.SendUserEmail.options.from_name_custom = this.form.trigger_actions.SendUserEmail.options.from_name;
                this.form.trigger_actions.SendUserEmail.options.from_name = 'custom';
              }
            }
          }
        }
        if (!this.form.trigger_actions.SendUserEmail.enabled) {
          this.form.trigger_actions.SendUserEmail.options = {
            template: 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
            from_name: 'helpdesk_name'
          };
        }
        if (this.account.incoming_account_type) {
          this.form.incoming_type = this.account.incoming_account_type;
          if (this.form.incoming_type === 'pop3') {
            this.form.in_pop3_account.host = this.account.incoming_account.host;
            this.form.in_pop3_account.port = this.account.incoming_account.port;
            this.form.in_pop3_account.secure_mode = this.account.incoming_account.secure_mode;
            this.form.in_pop3_account.user = this.account.incoming_account.user;
            this.form.in_pop3_account.password = this.account.incoming_account.password;
            if (this.form.in_pop3_account.secure_mode && this.form.in_pop3_account.secure_mode !== '') {
              this.form.in_pop3_account.secure = true;
            }
          }
          if (this.form.incoming_type === 'imap') {
            this.form.in_imap_account.host = this.account.incoming_account.host;
            this.form.in_imap_account.port = this.account.incoming_account.port;
            this.form.in_imap_account.secure_mode = this.account.incoming_account.secure_mode;
            this.form.in_imap_account.user = this.account.incoming_account.user;
            this.form.in_imap_account.password = this.account.incoming_account.password;
            this.form.in_imap_account.mode = this.account.incoming_account.mode || 'read';
            if (this.form.in_imap_account.secure_mode && this.form.in_imap_account.secure_mode !== '') {
              this.form.in_imap_account.secure = true;
            }
            if (this.account.incoming_account.read_mailbox && this.account.incoming_account.read_mailbox !== '') {
              this.form.in_imap_account.read_mailbox = this.account.incoming_account.read_mailbox;
              this.form.in_imap_account.read_mailbox_type = 'folder';
            }
            if (this.account.incoming_account.mode === 'archive') {
              this.form.in_imap_account.archive_mailbox = this.account.incoming_account.archive_mailbox;
            }
          }
          if (this.form.incoming_type === 'exchange') {
            this.form.in_exchange_account.host = this.account.incoming_account.host;
            this.form.in_exchange_account.user = this.account.incoming_account.user;
            this.form.in_exchange_account.password = this.account.incoming_account.password;
            this.form.in_exchange_account.mode = this.account.incoming_account.mode || 'read';
            if (this.account.incoming_account.read_mailbox && this.account.incoming_account.read_mailbox !== '') {
              this.form.in_exchange_account.read_mailbox = this.account.incoming_account.read_mailbox;
              this.form.in_exchange_account.read_mailbox_type = 'folder';
            }
            if (this.account.incoming_account.mode === 'archive') {
              this.form.in_exchange_account.archive_mailbox = this.account.incoming_account.archive_mailbox;
            }
          } else if (this.form.incoming_type === 'gmail') {
            this.form.in_gmail_account.password = this.account.incoming_account.password;
          }
        }
        if (this.account.outgoing_account_type) {
          this.form.outgoing_type = this.account.outgoing_account_type;
          if (this.form.outgoing_type === 'smtp') {
            this.form.out_smtp_account.host = this.account.outgoing_account.host;
            this.form.out_smtp_account.port = this.account.outgoing_account.port;
            this.form.out_smtp_account.secure_mode = this.account.outgoing_account.secure_mode;
            this.form.out_smtp_account.user = this.account.outgoing_account.user;
            this.form.out_smtp_account.password = this.account.outgoing_account.password;
            if (this.form.out_smtp_account.secure_mode && this.form.out_smtp_account.secure_mode !== '') {
              this.form.out_smtp_account.secure = true;
            }
          } else if (this.form.outgoing_type === 'gmail') {
            this.form.out_gmail_account.password = this.account.outgoing_account.password;
          }
        }
      }

      Admin_TicketAccounts_Form_EditTicketAccountModel.prototype.getFormData = function() {
        var department_id, form, options, trigger_actions, _ref, _ref1, _ref2;
        form = Util.clone(this.form, true);
        if (form.incoming_type === 'gmail') {
          form.in_gmail_account.user = form.address;
        }
        if (form.outgoing_type === 'gmail') {
          form.out_gmail_account.user = form.address;
        }
        if (form.incoming_type === 'imap') {
          if (form.in_imap_account.read_mailbox_type === 'inbox' || form.in_imap_account.read_mailbox === '') {
            form.in_imap_account.read_mailbox = null;
          }
          if (form.in_imap_account.mode === 'archive') {
            if (!this.form.in_imap_account.archive_mailbox || this.form.in_imap_account.archive_mailbox === '') {
              form.in_imap_account.mode = 'read';
              form.in_imap_account.archive_mailbox = '';
            }
          }
        }
        if (form.incoming_type === 'exchange') {
          if (form.in_exchange_account.read_mailbox_type === 'inbox' || form.in_exchange_account.read_mailbox === '') {
            form.in_exchange_account.read_mailbox = null;
          }
          if (form.in_exchange_account.mode === 'archive') {
            if (!this.form.in_exchange_account.archive_mailbox || this.form.in_exchange_account.archive_mailbox === '') {
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
        trigger_actions = [];
        department_id = parseInt(((_ref = this.form.trigger_actions.SetDepartment) != null ? (_ref1 = _ref.options) != null ? _ref1.department_id : void 0 : void 0) || 0);
        if (department_id) {
          trigger_actions.push({
            type: 'SetDepartment',
            options: {
              department_id: department_id
            }
          });
        }
        if ((_ref2 = this.form.trigger_actions.SendUserEmail) != null ? _ref2.enabled : void 0) {
          options = this.form.trigger_actions.SendUserEmail.options;
          trigger_actions.push({
            type: 'SendUserEmail',
            options: {
              template: options.template || 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
              from_name: options.from_name === 'custom' ? options.from_name_custom || '' : options.from_name || '',
              do_cc_users: true,
              from_account: 0
            }
          });
        }
        form.trigger_actions = trigger_actions;
        return form;
      };

      Admin_TicketAccounts_Form_EditTicketAccountModel.prototype.apply = function() {
        return this.account.address = this.form.address;
      };

      return Admin_TicketAccounts_Form_EditTicketAccountModel;

    })();
  });

}).call(this);

//# sourceMappingURL=EditTicketAccountModel.js.map
