define [
	'DeskPRO/Util/Util'
], (Util) ->
	class Admin_ChannelFacebook_FormModel_EditFacebookPageModel
		constructor: (@account) ->
			@form = {account: {}}
			@form.account.id = @account.id || 0
			@form.account.type = @account.type || "twilio"
			@form.account.identifier = @account.identifier || ""
			@form.account.phone_number = @account.phone_number || ""
			@form.account.params = @account.params || {}
			@form.account.is_enabled = if Util.isEmpty(@account.is_enabled) then false else @account.is_enabled
			@form.account.is_connected = if Util.isEmpty(@account.is_connected) then false else @account.is_connected
			@form.account.is_tested = if Util.isEmpty(@account.is_tested) then false else @account.is_tested

		setAccountData: (data) ->
			@form.account = data
			if Util.isEmpty(data.phone_number) and not Util.isEmpty(data.params?.numbers)
				data.phone_number = data.params.numbers[0].number

		getFormData: ->
			form = Util.clone(@form, true)
			return form.account

		clearCredentials: ->
			@markConnected(false)

		markConnected: (isConnected) ->
			@form.account.is_connected = isConnected
			if not isConnected
				@markTested(false)
				@form.account.identifier = null
				@form.account.is_enabled = false

		markTested: (isTested) ->
			@form.account.is_tested = isTested
			if not isTested
				@form.account.is_enabled = false

		setFriendlyName: (name) ->
			@form.account.identifier = name
