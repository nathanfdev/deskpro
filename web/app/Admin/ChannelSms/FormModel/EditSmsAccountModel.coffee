define [
	'DeskPRO/Util/Util'
], (Util) ->
	class Admin_ChannelSms_FormModel_EditSmsAccountModel
		constructor: (@account) ->
			@form = {account: {}}
			@form.account.id = @account.id || 0
			@form.account.type = @account.type || "twilio"
			@form.account.params = @account.params || { sid: 'AC82e05c22887c7a336941107979bc9709', auth_token: '9755c4ee6ab8e43c7fca39a38f664a6e'}
			@form.account.is_enabled = @account.is_enabled || true
			@form.account.is_connected = @account.is_connected || false
			@form.account.is_tested = @account.is_enabled || false
			@form.account.phone_number = @account.phone_number || null
			@form.account.identifier = @account.identifier || ""
			@form.numbers = if @form.account.phone_number?.length then [@account.phone_number] else []

		getFormData: ->
			form = Util.clone(@form, true)
			return form

		getConnectData: ->
			form = Util.clone(@form, true)

			return {
				type: form.account.type
				params: form.account.params
			}

		clearCredentials: ->
			@markConnected(false)

		markConnected: (isConnected) ->
			@form.account.is_connected = isConnected
			@form.account.is_tested = false
			if not isConnected
				@setNumbers([])
				@form.account.identifier = null

		setNumbers: (numbers) ->
			if not @form.account.phone_number?.length then @form.account.phone_number = numbers[0].phone_number
			@form.numbers = numbers

		setFriendlyName: (name) ->
			@form.account.identifier = name
