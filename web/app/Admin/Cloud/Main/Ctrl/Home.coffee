define [
	'Admin/Main/Ctrl/Base',
	'DeskPRO/Util/Strings'
], (
	Admin_Ctrl_Base,
	Strings
) ->
	class Admin_Cloud_Main_Ctrl_Home extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Cloud_Main_Ctrl_Home'
		@CTRL_AS   = 'Home'
		@DEPS      = ['$http']

		init: ->
			@online_agents = []
			@offline_agents = []
			@$scope.new_agent = {}
			return

		initialLoad: ->
			promise = @Api.sendDataGet({
				agents:      '/agents',
				lastLogin:   '/me/last-login',
				quickStats:  '/tickets/quick-stats',
				lic_info:    '/dp_license'
			}).then( (result) =>
				data = result.data
				@online_agents  = []
				@offline_agents = []
				@quick_stats    = result.data.quickStats
				@last_login     = result.data.lastLogin.last_login

				if @last_login
					@last_login.date_created_d = new Date(@last_login.date_created_ts * 1000)

				@license = result.data.lic_info.license

				for agent in data.agents.agents
					if agent.is_online_now or agent.id == DP_PERSON_ID
						@online_agents.push(agent)
					else
						@offline_agents.push(agent)
			)

			return promise

		###
		# Saves new agent form
		###
		addNewAgent: ->
			@$scope.created_agent = null

			postData = {
				agent: {
					name: Strings.trim(@$scope.new_agent.name || ''),
					emails: [Strings.trim(@$scope.new_agent.email || '')]
				}
			}

			@$scope.new_agent.errors = {
				name: !postData.agent.name,
				email: postData.agent.emails[0].indexOf('@') == -1
			}

			if @$scope.new_agent.errors.name or @$scope.new_agent.errors.email
				return

			@startSpinner('saving_new_agent')
			@Api.sendPutJson('/agents', postData).then(=>
				@stopSpinner('saving_new_agent').then(=>
					@$scope.created_agent = @$scope.new_agent
					@$scope.new_agent = {}
				)
			)

		###
		# Sends support request
		###
		sendSupportRequest: ->
			submit_ticket = @$scope.submit_ticket

			contact = {
				subject: Strings.trim(submit_ticket.subject || ''),
				message: Strings.trim(submit_ticket.message || '')
				email:   Strings.trim(submit_ticket.email   || '')
			}

			if not contact.message
				@$scope.submit_ticket_message_error = true
				return

			if @$scope.submit_ticket_defaultemail or contact.email.indexOf('@') == -1
				delete contact.email
				@$scope.submit_ticket_defaultemail = true

			@startSpinner('sending_support_request')
			@Api.sendPostJson('/dp_license/support-request', { contact: contact}).then( =>
				@stopSpinner('sending_support_request').then(=>
					@$scope.support_sent = true
				)
			)

	Admin_Cloud_Main_Ctrl_Home.EXPORT_CTRL()