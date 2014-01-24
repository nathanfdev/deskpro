define ->
	class Admin_TicketAccounts_Form_EditTicketAccountModel
		constructor: (@account) ->
			@form = {}
			@form.address          = @account.address
			@form.incoming_type    = 'pop3'
			@form.in_gmail_account = {}
			@form.in_pop3_account  = {}
			@form.in_imap_account  = {}

			@form.outgoing_type     = 'mail'
			@form.out_gmail_account = {}
			@form.out_smtp_account  = {}

			if @account.other_addresses.length
				@form.with_email_aliases = true
				@form.other_addresses = @account.other_addresses.join(', ')
			else
				@form.with_email_aliases = false
				@form.other_addresses = ''

			#--------------------
			# Init in account
			#--------------------

			if @account.incoming_account_type
				@form.incoming_type = @account.incoming_account_type

				if @form.incoming_type == 'pop3'
					@form.in_pop3_account.host     = @account.incoming_account.host
					@form.in_pop3_account.port     = @account.incoming_account.port
					@form.in_pop3_account.secure   = @account.incoming_account.secure
					@form.in_pop3_account.username = @account.incoming_account.username
					@form.in_pop3_account.password = @account.incoming_account.password
				else if @form.incoming_type == 'gmail'
					@form.in_gmail_account.password = @account.incoming_account.password

			#--------------------
			# Init out account
			#--------------------

			if @account.outgoing_account_type
				@form.outgoing_type = @account.outgoing_account_type

				if @form.outgoing_type == 'smtp'
					@form.out_smtp_account.host     = @account.outgoing_account.host
					@form.out_smtp_account.port     = @account.outgoing_account.port
					@form.out_smtp_account.secure   = @account.outgoing_account.secure
					@form.out_smtp_account.username = @account.outgoing_account.username
					@form.out_smtp_account.password = @account.outgoing_account.password
				else if @form.outgoing_type == 'gmail'
					@form.out_gmail_account.password = @account.outgoing_account.password

		getFormData: ->
			if @form.incoming_type == 'gmail'
				@form.in_gmail_account.user = @form.address
			if @form.outgoing_type == 'gmail'
				@form.out_gmail_account.user = @form.address

			return @form

		apply: ->
			@account.address = @form.address