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
        this.form.outgoing_type = 'mail';
        this.form.out_gmail_account = {};
        this.form.out_smtp_account = {};
        if (this.account.incoming_account_type) {
          this.form.incoming_type = this.account.incoming_account_type;
          if (this.form.incoming_type === 'pop3') {
            this.form.in_pop3_account.host = this.account.incoming_account.host;
            this.form.in_pop3_account.port = this.account.incoming_account.port;
            this.form.in_pop3_account.secure = this.account.incoming_account.secure;
            this.form.in_pop3_account.username = this.account.incoming_account.username;
            this.form.in_pop3_account.password = this.account.incoming_account.password;
          } else if (this.form.incoming_type === 'gmail') {
            this.form.in_gmail_account.password = this.account.incoming_account.password;
          }
        }
        if (this.account.outgoing_account_type) {
          this.form.outgoing_type = this.account.outgoing_account_type;
          if (this.form.outgoing_type === 'smtp') {
            this.form.out_smtp_account.host = this.account.outgoing_account.host;
            this.form.out_smtp_account.port = this.account.outgoing_account.port;
            this.form.out_smtp_account.secure = this.account.outgoing_account.secure;
            this.form.out_smtp_account.username = this.account.outgoing_account.username;
            this.form.out_smtp_account.password = this.account.outgoing_account.password;
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
