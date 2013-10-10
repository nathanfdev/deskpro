define ->
	class Admin_TicketAccounts_Form_EditTicketAccountModel
		constructor: (@account) ->
			@form = {}
			@form.email_address = @account.email_address
			@form.connection_type  = 'pop3'
			@form.in_gmail_account = {}
			@form.in_pop3_account  = {}
			@form.in_imap_account  = {}

			@form.email_transport = {
				transport_type: 'mail',
				out_gmail_account: {},
				out_smtp_account: {}
			}

			#--------------------
			# Init account options
			#--------------------

			if @account.connection_type
				@form.connection_type = @account.connection_type

				if @form.connection_type == 'pop3'
					@form.in_pop3_account.host     = @account.linked_transport.transport_options.host
					@form.in_pop3_account.port     = @account.linked_transport.transport_options.port
					@form.in_pop3_account.secure   = @account.linked_transport.transport_options.secure
					@form.in_pop3_account.username = @account.linked_transport.transport_options.username
					@form.in_pop3_account.password = @account.linked_transport.transport_options.password
				else if @form.connection_type == 'pop3'
					@form.in_imap_account.host     = @account.linked_transport.transport_options.host
					@form.in_imap_account.port     = @account.linked_transport.transport_options.port
					@form.in_imap_account.secure   = @account.linked_transport.transport_options.secure
					@form.in_imap_account.username = @account.linked_transport.transport_options.username
					@form.in_imap_account.password = @account.linked_transport.transport_options.password
				else if @form.connection_type == 'gmail'
					@form.in_gmail_account.password = @account.linked_transport.transport_options.password

			#--------------------
			# Init linked account options
			#--------------------

			if @account.linked_transport
				@form.email_transport.transport_type = @account.linked_transport.transport_type

				if @form.email_transport.transport_type == 'gmail'
					@form.email_transport.out_gmail_account.password = @account.linked_transport.transport_options.password
				else if @form.email_transport.transport_type == 'smtp'
					@form.email_transport.out_smtp_account.host     = @account.linked_transport.transport_options.host
					@form.email_transport.out_smtp_account.port     = @account.linked_transport.transport_options.port
					@form.email_transport.out_smtp_account.secure   = @account.linked_transport.transport_options.secure
					@form.email_transport.out_smtp_account.username = @account.linked_transport.transport_options.username
					@form.email_transport.out_smtp_account.password = @account.linked_transport.transport_options.password

		getFormData: ->
			if @form.connection_type == 'gmail'
				@form.in_gmail_account.username = @form.email_address
			if @form.email_transport.transport_type == 'gmail'
				@form.email_transport.out_gmail_account.username = @form.email_address

			return @form