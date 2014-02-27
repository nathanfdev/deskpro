(function() {
  define(function() {
    var Admin_TicketAccounts_Form_EditTicketAccountModel;
    return Admin_TicketAccounts_Form_EditTicketAccountModel = (function() {
      function Admin_TicketAccounts_Form_EditTicketAccountModel(account) {
        this.account = account;
        this.form = {};
        this.form.address = this.account.address;
        this.form.incoming_type = 'pop3';
        this.form.in_gmail_account = {};
        this.form.in_pop3_account = {};
        this.form.in_imap_account = {};
        this.form.outgoing_type = 'mail';
        this.form.out_gmail_account = {};
        this.form.out_smtp_account = {};
        if (this.account.other_addresses && this.account.other_addresses.length) {
          this.form.with_email_aliases = true;
          this.form.other_addresses = this.account.other_addresses.join(', ');
        } else {
          this.form.with_email_aliases = false;
          this.form.other_addresses = '';
        }
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
        if (this.form.incoming_type === 'gmail') {
          this.form.in_gmail_account.user = this.form.address;
        }
        if (this.form.outgoing_type === 'gmail') {
          this.form.out_gmail_account.user = this.form.address;
        }
        return this.form;
      };

      Admin_TicketAccounts_Form_EditTicketAccountModel.prototype.apply = function() {
        return this.account.address = this.form.address;
      };

      return Admin_TicketAccounts_Form_EditTicketAccountModel;

    })();
  });

}).call(this);

//# sourceMappingURL=EditTicketAccountModel.js.map
