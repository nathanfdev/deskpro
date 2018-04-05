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
    @DEPS      = ['$http', 'DpLicense', 'Growl']

    init: ->
      @online_agents = []
      @offline_agents = []
      @$scope.new_agent = {}
      @$scope.hide_admin_upgrade_notice = window.hide_admin_upgrade_notice || false
      @$scope.readable_config = false
      @$scope.missconfigure_web_root = false
      @$scope.missed_fk_found = false
      @$scope.valid_url = true

      @online_agents  = []
      @offline_agents = []
      @unactive_agents = []
      @agentsMap = {}
      @features = {}
      @service =
        agents: @DataService.get 'Agents'
      @$scope.keys = Object.keys

      @loadConfigPhpTest();
      @loadMethodTests()
      return

    initialLoad: ->
      w = window
      if w.parent != window
        w = w.parent
      l = w.location
      if l.protocol.replace(':', '') == 'http'
        defaultPort = 80
      else
        defaultPort = 443
      @Api.sendGet('check_url', {scheme: l.protocol.replace(':', ''), host: encodeURIComponent(l.hostname), port: l.port || defaultPort}).then((res) =>
        @$scope.valid_url = res.data.valid
      )

      @refreshAgents()
      promise = @Api.sendDataGet({
        lastLogin:   '/me/last-login',
        versionInfo: '/dp_license/version-info'
      }).then( (result) =>
        @version_info   = result.data.versionInfo
        @last_login     = result.data.lastLogin.last_login

        if @last_login
          @last_login.date_created_d = new Date(@last_login.date_created_ts * 1000)
      )

      @Api.sendDataGet({
        cronStatus:   '/server/cron-status',
        errorStatus:  '/server/error-status',
        apcStatus:    '/server/apc-status',
        quickStats:   '/tickets/quick-stats',
      }).then( (result) =>
        @cron_status    = result.data.cronStatus
        @error_status   = result.data.errorStatus
        @apc_status     = result.data.apcStatus
        @quick_stats    = result.data.quickStats

        problem_triggers = [
          @cron_status?.is_problem,
          @error_status?.error_count > 0,
          @error_status?.error_log_size,
          @error_status?.gateway_error_count > 0,
          @error_status?.sendmail_error_count > 0,
          @apc_status?.is_problem
        ]
        @is_server_problem = problem_triggers.filter((x) -> return !!x).length > 0
        @$scope.missed_fk_found = @error_status.missed_fk_found
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

      @Api2.sendGet('features').then( (res) =>
        res.data.data?.forEach( (feature) =>
          if feature.processing
            @pollFeatures()
          @features[feature.id] = feature;
        )
      )

      return promise

    deleteLogFile: (e) =>
      @Api.sendDelete('/server_error_logs').then( () =>
        @error_status.error_log_size = false;

        problem_triggers = [
          @cron_status?.is_problem,
          @error_status?.error_count > 0,
          @error_status?.gateway_error_count > 0,
          @error_status?.sendmail_error_count > 0,
          @apc_status?.is_problem
        ]
        @is_server_problem = problem_triggers.filter((x) -> return !!x).length > 0
      )


    pollFeatures: =>
      if not @pollTimer
        @pollTimer = setTimeout @actualPoll, 60000

    actualPoll: =>
      @Api2.sendGet('features').then( (res) =>
        @pollTimer = null
        res.data.data.forEach((feature) =>
          if @features[feature.id].processing == true && feature.processing == false
            @Growl.success 'Feature ' + feature.title + ' successfully ' + if feature.enabled then 'enabled' else 'disabled' + '!'
          if feature.processing then @pollFeatures()
          @features[feature.id] = feature
        )
      )

    # todo just move agent object from one array to another when BaseListEdit will be able to handle model objects updates after reload
    refreshAgents: ->
      @service.agents.all(true).then (agents) =>
        for agent in agents
          # if we see this agent for the first time
          if !@agentsMap[agent.id]?
            if agent.is_online_now or agent.id == DP_PERSON_ID
              @online_agents.push(agent)
              @agentsMap[agent.id] = 'online_agents'
            else if !agent.date_last_login?
              @unactive_agents.push(agent)
              @agentsMap[agent.id] = 'unactive_agents'
            else
              @offline_agents.push(agent)
              @agentsMap[agent.id] = 'offline_agents'

          # or we already stored this agent in @agentsMap
          else
            # state - is the new state of agent
            state = 'offline_agents'
            if agent.is_online_now or agent.id == DP_PERSON_ID
              state = 'online_agents'
            else if !agent.date_last_login?
              state = 'unactive_agents'

            # if agent state changed
            if @agentsMap[agent.id] != state
              list = @[@agentsMap[agent.id]]
              index = -1

              # then find agent index in old state array
              for _agent, i in list
                if _agent.id == agent.id
                  index = i
                  break

              # then remove it if found
              if -1 != index
                list.splice index, 1

              # and push to new state array
              @[state].push agent
              @agentsMap[agent.id] = state


        @$timeout (=> @refreshAgents()), 60 * 1000

    loadConfigPhpTest: ->
      checkUrl = DP_BASE_URL + 'app/run/test_ping.html'

      @$scope.config_php_url = location.protocol+'//'+location.hostname+(if location.port then ':' + location.port else '')+checkUrl

      @$http({
        method: 'GET',
        url: checkUrl + '?x=' + ((new Date()).getTime()),
        responseType: "text",
        cache: false
      }).success((res) =>
        return if not res or res.success
        if typeof res is 'string' and res.indexOf('DESKPRO_PONG') != -1 and res.indexOf('<!--') != -1
          @$scope.readable_config = true
        if typeof res is 'string' and res.indexOf('OK') != 0
          @$scope.missconfigured_web_root = true
      ).error( =>
        @$scope.missconfigured_web_root = true
      )

    loadMethodTests: ->
      promises = []
      http_method = {}
      $http = @$http

      checkUrl = DP_BASE_URL + '__serverinfo/check_http_methods?x=' + ((new Date()).getTime())

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
      @Api.sendPutJson('/agents', postData).then( (res) =>
        @unactive_agents.push res.data
        @stopSpinner('saving_new_agent').then(=>
          @$scope.created_agent = @$scope.new_agent
          @$scope.new_agent = {}
        )
      , (res) =>
        @stopSpinner('saving_new_agent', true)
        if res.data.error_code and res.data.error_code == 'license_exceeded'
          @DpLicense.openUpgradeLicense('upgrade_plan').then(=>
            @addNewAgent()
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
