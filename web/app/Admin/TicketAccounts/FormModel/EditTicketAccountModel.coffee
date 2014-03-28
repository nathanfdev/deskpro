define [
	'DeskPRO/Util/Util'
], (Util) ->
	class Admin_TicketAccounts_Form_EditTicketAccountModel
		constructor: (@account, deps, trigger) ->
			@form = {}
			@form.account_type     = @account.account_type || 'tickets';
			@form.address          = @account.address
			@form.incoming_type    = 'pop3'
			@form.in_gmail_account = {}
			@form.in_pop3_account  = {}
			@form.in_imap_account  = {}

			if @account.other_addresses and @account.other_addresses.length
				@form.with_email_aliases = true
				@form.other_addresses = @account.other_addresses.join(', ')
			else
				@form.with_email_aliases = false
				@form.other_addresses = ''

			#--------------------
			# Trigger
			#--------------------

			@form.trigger_actions = {
				SetDepartment: {
					options: {
						department_id: '0'
					}
				},
				SendUserEmail: {
					enabled: false,
					options: {}
				}
			}

			if deps and deps.length
				@form.trigger_actions.SetDepartment.options.department_id = deps[0].id+''

			if trigger and trigger.actions?.actions?.length
				for act in trigger.actions.actions
					if act.type == 'SetDepartment'
						@form.trigger_actions.SetDepartment.options = act.options
					else if act.type == 'SendUserEmail'
						@form.trigger_actions.SendUserEmail.enabled = true
						@form.trigger_actions.SendUserEmail.options = act.options

						if ['helpdesk_name', 'site_name'].indexOf(@form.trigger_actions.SendUserEmail.options.from_name) == -1
							@form.trigger_actions.SendUserEmail.options.from_name_custom = @form.trigger_actions.SendUserEmail.options.from_name
							@form.trigger_actions.SendUserEmail.options.from_name = 'custom'

			if not @form.trigger_actions.SendUserEmail.enabled
				@form.trigger_actions.SendUserEmail.options = {
					template: 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
					from_name: 'helpdesk_name'
				}

			@form.outgoing_type     = 'mail'
			@form.out_gmail_account = {}
			@form.out_smtp_account  = {}

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

			form = Util.clone(@form, true)

			if form.incoming_type == 'gmail'
				form.in_gmail_account.user = form.address
			if form.outgoing_type == 'gmail'
				form.out_gmail_account.user = form.address

			trigger_actions = []
			department_id = parseInt(@form.trigger_actions.SetDepartment?.options?.department_id || 0)
			if department_id
				trigger_actions.push({
					type: 'SetDepartment',
					options: {
						department_id: department_id
					}
				})
			if @form.trigger_actions.SendUserEmail?.enabled
				options = @form.trigger_actions.SendUserEmail.options

				trigger_actions.push({
					type: 'SendUserEmail',
					options: {
						template:  options.template || 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
						from_name: if options.from_name == 'custom' then (options.from_name_custom || '') else (options.from_name || ''),
						do_cc_users: true,
						from_account: 0
					}
				})

			form.trigger_actions = trigger_actions

			return form

		apply: ->
			@account.address = @form.address