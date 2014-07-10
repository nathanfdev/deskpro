define [
	'Admin/Main/Ctrl/Base',
	'DeskPRO/Util/Strings'
], (
	Admin_Ctrl_Base,
	Strings
) ->
	class Admin_Main_Ctrl_Home extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_Home'
		@CTRL_AS   = 'Home'
		@DEPS      = ['$http']

		init: ->
			@online_agents = []
			@offline_agents = []
			@$scope.new_agent = {}
			@$scope.hide_admin_upgrade_notice = window.hide_admin_upgrade_notice || false
			@loadMethodTests()
			return

		initialLoad: ->
			promise = @Api.sendDataGet({
				agents:      '/agents',
				lastLogin:   '/me/last-login',
				cronStatus:  '/server/cron-status',
				errorStatus: '/server/error-status',
				apcStatus:   '/server/apc-status',
				versionInfo: '/dp_license/version-info',
				quickStats:  '/tickets/quick-stats'
			}).then( (result) =>
				data = result.data
				@online_agents  = []
				@offline_agents = []
				@cron_status    = result.data.cronStatus
				@error_status   = result.data.errorStatus
				@apc_status     = result.data.apcStatus
				@version_info   = result.data.versionInfo
				@quick_stats    = result.data.quickStats
				@last_login     = result.data.lastLogin.last_login

				if @last_login
					@last_login.date_created_d = new Date(@last_login.date_created_ts * 1000)

				problem_triggers = [
					@cron_status.is_problem,
					@error_status.error_count > 0,
					@error_status.gateway_error_count > 0,
					@error_status.sendmail_error_count > 0,
					@apc_status.is_problem
				]
				@is_server_problem = problem_triggers.filter((x) -> return !!x).length > 0


				for agent in data.agents.agents
					if agent.is_online_now or agent.id == DP_PERSON_ID
						@online_agents.push(agent)
					else
						@offline_agents.push(agent)
			)

			# Get news and version info in parallel
			@Api.sendDataGet({
				latestVersion: '/dp_license/latest-version-info',
				news: '/dp_license/news'
			}).then( (result) =>
				if not result.data.latestVersion?.version_info?
					@latest_version_status = "error"
				else
					@latest_version_status = "okay"
					@latest_version = result.data.latestVersion.version_info
					@latest_version.count_behind = parseInt(result.data.latestVersion.version_info.count_behind) || 0

				if not result.data.news?.news?
					@news_status = "error"
				else
					@news_status = "okay"
					@news = result.data.news.news
			)

			return promise

		loadMethodTests: ->
			promises = []
			http_method = {}
			$http = @$http

			checkUrl = DP_BASE_URL + 'index.php?_sys=check_http_method&x=' + ((new Date()).getTime())

			makeCheck = (type) ->
				typeU = type.toUpperCase()
				p = $http({
					method: typeU,
					url: checkUrl,
					responseType: "text",
					cache: false
				})

				p.success( (res) ->
					if not res then res = ''
					if res.indexOf("HTTP_METHOD_#{typeU}") != -1
						http_method[type] = true
					else
						http_method[type] = false
				)
				p.error(-> http_method[type] = false)

				return p

			promises.push makeCheck('get')
			promises.push makeCheck('post')
			promises.push makeCheck('put')
			promises.push makeCheck('delete')

			masterP = @$q.all(promises)
			checkRes = =>
				@$scope.http_method_checks = http_method

				any = false
				for own k, v of http_method
					if not v
						any = true
						break

				@$scope.http_method_errors = any

			masterP.then(checkRes, checkRes)

		###
		# Saves new agent form
		###
		addNewAgent: ->
			@$scope.created_agent = null

			postData = {
				quick_add: true
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

		###
		# Dismiss ugrade notice
		###
		dismissUpgradeNotice: (value) ->
			$('#admin_upgrade_notice').slideUp()
			window.hide_admin_upgrade_notice = true
			@Api.sendPost('/settings/values/core.admin_upgrade_notice', {
				value: value
			})


	Admin_Main_Ctrl_Home.EXPORT_CTRL()