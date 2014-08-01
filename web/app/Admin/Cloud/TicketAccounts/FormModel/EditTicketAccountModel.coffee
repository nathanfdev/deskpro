define [
	'DeskPRO/Util/Util',
	'Admin/TicketAccounts/FormModel/EditTicketAccountModel',
], (Util, BaseFormModel) ->
	class Admin_Cloud_TicketAccounts_FormModel_EditTicketAccountModel extends BaseFormModel
		constructor: (@account, deps, trigger) ->
			super(@account, deps, trigger)

			if @form.address and @form.address.indexOf('@') != -1
				@form.address_name = @form.address.split('@')[0]

			@form.use_custom_email_address = false

			if @account.other_addresses and @account.other_addresses.length and @account.options.custom_email_address
				@form.custom_email_address = @account.other_addresses.shift()
				@form.use_custom_email_address = true

			if @account.other_addresses and @account.other_addresses.length
				@form.with_email_aliases = true
				@form.other_addresses = @account.other_addresses.join(', ')
			else
				@form.with_email_aliases = false
				@form.other_addresses = ''

		getFormData: ->
			form = super()

			if not form.with_email_aliases
				form.other_addresses = ''

			return form

		apply: ->
			@account.address = @form.address_name + '@' + window.DPC_SITE_DOMAIN
			if not @account.options?
				@account.options = {}

			if @form.use_custom_email_address and @form.custom_email_address
				@account.options.custom_email_address = @form.custom_email_address
			else
				@account.options.custom_email_address = null