define [
	'DeskPRO/Util/Util'
], (Util) ->
	class Admin_ChannelSms_FormModel_EditSmsAccountModel
		constructor: (@account) ->
			@form = {account: {}}
			@form.account.id = @account.id || 0
			@form.account.type = @account.type || "twilio"
			@form.account.params = @account.params || { sid: 'AC82e05c22887c7a336941107979bc9709', auth_token: '9755c4ee6ab8e43c7fca39a38f664a6e'}
			@form.account.is_enabled = if Util.isEmpty(@account.is_enabled) then false else @account.is_enabled
			@form.account.is_connected = if Util.isEmpty(@account.is_connected) then false else @account.is_connected
			@form.account.is_tested = if Util.isEmpty(@account.is_tested) then false else @account.is_tested
			@form.numbers = if @account.phone_number?.length then [{number: @account.phone_number, display_name: @account.phone_number}] else []
			@form.account.phone_number = @account.phone_number || null
			@form.account.identifier = @account.identifier || ""

		getFormData: ->
			form = Util.clone(@form, true)
			return form

		getConnectData: ->
			form = Util.clone(@form, true)
			return {
				type: form.account.type
				params: form.account.params
				phone_number: form.account.phone_number
			}

		clearCredentials: ->
			@markConnected(false)

		markConnected: (isConnected) ->
			@form.account.is_connected = isConnected
			if not isConnected
				@setNumbers([])
				@markTested(false)
				@form.account.identifier = null
				@form.account.is_enabled = false

		markTested: (isTested) ->
			@form.account.is_tested = isTested
			if not isTested
				@form.account.is_enabled = false

		setNumbers: (numbers) ->
			if Util.isEmpty(@form.account.phone_number) then @form.account.phone_number = numbers[0].number
			@form.numbers = numbers

		setFriendlyName: (name) ->
			@form.account.identifier = name
