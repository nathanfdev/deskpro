(function() {
  define(function() {
    var Admin_TicketAccounts_Form_EditTicketAccountModel;
    return Admin_TicketAccounts_Form_EditTicketAccountModel = (function() {
      function Admin_TicketAccounts_Form_EditTicketAccountModel(account) {
        this.account = account;
        this.form = {};
        this.form.email_address = this.account.email_address;
        this.form.connection_type = 'pop3';
        this.form.in_gmail_account = {};
        this.form.in_pop3_account = {};
        this.form.email_transport = {
          transport_type: 'mail',
          out_gmail_account: {},
          out_smtp_account: {}
        };
        if (this.account.connection_type) {
          this.form.connection_type = this.account.connection_type;
          if (this.form.connection_type === 'pop3') {
            this.form.in_pop3_account.host = this.account.linked_transport.transport_options.host;
            this.form.in_pop3_account.port = this.account.linked_transport.transport_options.port;
            this.form.in_pop3_account.secure = this.account.linked_transport.transport_options.secure;
            this.form.in_pop3_account.username = this.account.linked_transport.transport_options.username;
            this.form.in_pop3_account.password = this.account.linked_transport.transport_options.password;
          } else if (this.form.connection_type === 'gmail') {
            this.form.in_gmail_account.password = this.account.linked_transport.transport_options.password;
          }
        }
        if (this.account.linked_transport) {
          this.form.email_transport.transport_type = this.account.linked_transport.transport_type;
          if (this.form.email_transport.transport_type === 'gmail') {
            this.form.email_transport.out_gmail_account.password = this.account.linked_transport.transport_options.password;
          } else if (this.form.email_transport.transport_type === 'smtp') {
            this.form.email_transport.out_smtp_account.host = this.account.linked_transport.transport_options.host;
            this.form.email_transport.out_smtp_account.port = this.account.linked_transport.transport_options.port;
            this.form.email_transport.out_smtp_account.secure = this.account.linked_transport.transport_options.secure;
            this.form.email_transport.out_smtp_account.username = this.account.linked_transport.transport_options.username;
            this.form.email_transport.out_smtp_account.password = this.account.linked_transport.transport_options.password;
          }
        }
      }

      Admin_TicketAccounts_Form_EditTicketAccountModel.prototype.getFormData = function() {
        if (this.form.connection_type === 'gmail') {
          this.form.in_gmail_account.username = this.form.email_address;
        }
        if (this.form.email_transport.transport_type === 'gmail') {
          this.form.email_transport.out_gmail_account.username = this.form.email_address;
        }
        return this.form;
      };

      return Admin_TicketAccounts_Form_EditTicketAccountModel;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=EditTicketAccountModel.js.map
*/