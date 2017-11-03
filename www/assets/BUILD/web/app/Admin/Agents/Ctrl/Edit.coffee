define [
  'DeskPRO/Util/Strings',
  'Admin/Main/Ctrl/Base',
  'Admin/Agents/FormModel/EditAgentModel',
  'Admin/Agents/FormModel/EditAgentNotifPrefs'
], (
  Strings,
  Admin_Ctrl_Base,
  EditAgentModel,
  EditAgentNotifPrefs
) ->
  class Admin_Agents_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Agents_Ctrl_Edit'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['DpLicense']

    init: ->
      window.AGENT_CTRL = this
      @agentId = parseInt(@$stateParams.id)
      @created_agent = @$stateParams.created_agent
      @form = {email_primary: '', emails_list: []}
      @hasPermOverrides = false
      @hasDepOverrides = false
      @default_phone_number_region = 'US'
      @service =
        agents: @DataService.get 'Agents'
      @all_perms =
        perms: {}
        deps_perms:
          tickets: {assign: true, full: true}
          chat: {full: true}

      @$scope.$watch('EditCtrl.form.emails_list', (emails_list) =>
        @email_sysaccount_error = false
        if not emails_list then return
        if not @form.email_primary or @form.email_primary == '' or emails_list.indexOf(@form.email_primary) == -1
          if emails_list.length
            @form.email_primary = emails_list[0]
          else
            @form.email_primary = ''
      )

      @$scope.$watch('EditCtrl.form.zones.admin', =>
        return if not @form?.zones?
        @form.zones.reports = @form.zones.reports || @form.zones.admin
      )
      @$scope.$watch('EditCtrl.form.zones.reports', =>
        return if not @form?.zones?
        @form.zones.reports = @form.zones.reports || @form.zones.admin
      )

      @$scope.show_selected_permissions = false
      @$scope.show_selected_teams = false
      @$scope.selectedFilter = (show_selected) ->
        return (itm) ->
          return !show_selected || itm.value

      return

    initialLoad: ->
      if @agentId
        promise = @Api.sendDataGet({
          agent: "/agents/#{@agentId}?extended=1",
          teams: "/agent_teams",
          groups: "/agent_groups",
          groupPerms: "/agent_groups/all/permissions",
          notif_prefs_table: "/agents/#{@agentId}/notify-prefs/get-tables",
          ticketDeps: "/ticket_deps?with_perms=1",
          chatDeps: "/chat_deps?with_perms=1",
          default_country: "/settings/values/core.default_country_code"
        })
      else
        promise = @Api.sendDataGet({
          teams: "/agent_teams",
          groups: "/agent_groups",
          groupPerms: "/agent_groups/all/permissions",
          notif_prefs_table: "/agents/0/notify-prefs/get-tables",
          ticketDeps: "/ticket_deps?with_perms=1",
          chatDeps: "/chat_deps?with_perms=1",
          default_country: "/settings/values/core.default_country_code"
        })

      promise.then( (result) =>
        if @agentId
          @agent = result.data.agent.agent
          @perm_form = result.data.agent.perm_overrides
        else
          @agent = {
            id: 0,
            name: '',
            email: {},
            teams: [],
            usergroups: []
          }
          @perm_form = null

        if result.data.default_country.value
          @default_phone_number_region = result.data.default_country.value
        @primary_phone_number_region = result.data.default_country.value || @default_phone_number_region

        @teams  = result.data.teams.agent_teams
        @groups = result.data.groups.groups
        @groupPerms = result.data.groupPerms.groups

        @ticketDeps = result.data.ticketDeps.departments
        @chatDeps   = result.data.chatDeps.departments

        @agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table)
        @notif_prefs = @agentNotifPrefsModel.prefsTable

        @agentFormModel = new EditAgentModel(@agent, @groups, @teams, @primary_phone_number_region)
        @form = @agentFormModel.form

        if @form.primary_phone
          @primary_phone_number_region = @form.primary_phone.region

        @$scope.$watch('EditCtrl.form.agent_groups', =>
          @updateEffectiveUgPerms()
          @updateAllPermsState()
        , true)

        #--------------------
        # Departments
        #--------------------

        @deps_perms = @parseDepPermOverrides(@agentId, @ticketDeps, @chatDeps)
        @$timeout(=> @updateHasPermOverridesStatus())
      )
      return promise

    hasAnyDepTicketsPerms: ->
      for own perm, obj of @deps_perms['tickets']
        if obj.assign
          return true
      return false

    canCreateNewTicket: ->
      return @perm_form && @perm_form['ticket']? && @perm_form['ticket'].create

    changeUse: (type) ->
      return if !@perm_form[type]? || true == @perm_form[type].use
      for own perm of @perm_form[type]
        @perm_form[type][perm] = false



    changeAllPerms: (type, section) ->
      return if !@perm_form? || !@deps_perms?

      if 'perms' == type
        for own perm of @perm_form[section]
          if not @ugEffectivePerms[section]?[perm]? or not @ugEffectivePerms[section][perm]
            @perm_form[section][perm] = @all_perms[type][section]

        if 'people' == section
          @changeAllPerms('perms', 'org')

      else if 'deps_perms_tickets' == type
        for own dep of @deps_perms.tickets
          if !@ugEffectiveDepPerms.tickets[dep]?[section]? || !@ugEffectiveDepPerms.tickets[dep][section]
            @deps_perms.tickets[dep][section] = @all_perms.deps_perms.tickets[section]

      else if 'deps_perms_chat' == type
        for own dep of @deps_perms.chat
          if !@ugEffectiveDepPerms.chat[dep]?[section]? || !@ugEffectiveDepPerms.chat[dep][section]
            @deps_perms.chat[dep][section] = @all_perms.deps_perms.chat[section]

      @updateHasPermOverridesStatus()



    updateAllPermsState: ->
      return if !@perm_form?

      # check "use" state first
      for own section, perms of @perm_form
        for own perm of perms
          if 'use' != perm && perms.use? && (perms[perm] || @ugEffectivePerms[section]?[perm])
            perms.use = true
            break

      # and this one is for "toggle all"
      for own section, perms of @perm_form
        enabled = true
        for own perm of perms
          if !perms[perm] && !@ugEffectivePerms[section]?[perm]
            enabled = false
            break
        @all_perms.perms[section] = enabled

      for own type, sections of @all_perms.deps_perms
        for own section of sections
          enabled = true
          for own dep of @deps_perms[type]
            if !@deps_perms[type][dep][section] && !@ugEffectiveDepPerms[type][dep][section]
              enabled = false
          @all_perms.deps_perms[type][section] = enabled



    ###
    # When usergroups are changed, we need to update the effective list of permissions
    ###
    updateEffectiveUgPerms: ->

      # todo this map should be loaded from server
      @ugEffectivePerms = {
        ticket: {}
        people: {}
        org: {}
        chat: {}
        publish: {}
        general: {}
        tasks: {}
        problems: {}
        snippet: {}
      }

      @ugEffectiveDepPerms = {
        tickets: {},
        chat: {}
      }

      if not @form.agent_groups then return

      groupIds = []
      for group in @form.agent_groups
        if group.value
          groupIds.push(group.id)

      for dep in @ticketDeps
        assign = false
        full = false

        if dep.permissions?.agentgroups
          perms = dep.permissions.agentgroups.filter((x) -> x.id in groupIds)
          for p in perms
            if p.name == 'full' then full = true else assign = true

        @ugEffectiveDepPerms.tickets[dep.id] = { assign: assign, full: full }

      for dep in @chatDeps
        full = false

        if dep.permissions?.agentgroups
          perms = dep.permissions.agentgroups.filter((x) -> x.id in groupIds)
          for p in perms
            full = true

        @ugEffectiveDepPerms.chat[dep.id] = { full: full }

      for info in @groupPerms
        if info.group.id in groupIds
          for own type, perms of info.perms
            for own pname, pval of perms
              if pval
                @ugEffectivePerms[type][pname] = pval

    hasSomePerms: (typename, permname) ->
      prefix = permname.replace(/(^.*?_).*?$/, '$1')
      suffix = permname.replace(/^.*?(_.*?)$/, '$1')
      return if not suffix or not (@ugEffectivePerms?[typename]? || @perm_form?[typename]?)

      if @ugEffectivePerms?[typename]?
        for own name, val of @ugEffectivePerms[typename]
          if val and (name.indexOf(suffix) != -1 and name.indexOf(prefix) == 0)
            return true

      if @perm_form?[typename]?
        for own name, val of @perm_form[typename]
          if val and (name.indexOf(suffix) != -1 and name.indexOf(prefix) == 0)
            return true

      return false

    ###
      # When a permission is updated, we need to update the hasPermOverrides status.
      # This is done by an ngChange on the permission toggles. We dont use a watch because
      # it can become too slow to watch the large graph of permissions.
    ###
    updateHasPermOverridesStatus: ->
      return if !@ugEffectivePerms? || !@ugEffectiveDepPerms?

      @hasPermOverrides = false
      run = =>
        for own type, perms of @perm_form
          for own permName, value of perms
            if value
              if not @ugEffectivePerms[type]?[permName]? or not @ugEffectivePerms[type][permName] or not @form.agent_groups.length
                @hasPermOverrides = true
                return
      run()

      @hasDepOverrides = false
      run = =>
        return if not @deps_perms or not @deps_perms.tickets
        for app in ['tickets', 'chat']
          for own depId, perms of @deps_perms[app]
            for own perm, value of perms
              if value
                if not @ugEffectiveDepPerms[app][depId][perm] or not @form.agent_groups.length
                  @hasDepOverrides = true
                  return
      run()

      @updateAllPermsState()


    ###
      # This does the actual removal of all perm overrides
    ###
    clearPermOverrides: =>
      for own type, perms of @perm_form
        for own permName, value of perms
          perms[permName] = false
      @hasPermOverrides = false

    ###
      # This does the actual removal of all depoverrides
    ###
    clearDepOverrides: =>
      for app in ['tickets', 'chat']
        for own depId, perms of @deps_perms[app]
          for own perm, value of perms
            @deps_perms[app][depId][perm] = false
      @hasDepOverrides = false


    ###
      # Shows the password reset modal
      ###
    showResetPassword: ->
      doReset = (setPassword) =>
        if not setPassword or not Strings.trim(setPassword)
          setPassword = ''

        return @Api.sendPostJson("/agents/#{@agentId}/reset-password", {
          set_password: setPassword
        })

      inst = @$modal.open({
        templateUrl: @getTemplatePath('Agents/reset-password-modal.html?' + (new Date()).getTime() ),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.password =
            mode: 'random',
            manual: ''

          $scope.dismiss = -> $modalInstance.dismiss()

          $scope.saveResetPassword = ->
            $scope.is_saving = true
            $scope.error = null

            if $scope.password.mode == 'set'
              doReset($scope.password.manual).then(
                => $modalInstance.close()
                (res) =>
                  $scope.is_saving = false
                  $scope.error = res.data.error_message
                  $scope.error_code = res.data.error_info.error_code
              )
            else
              doReset(false).then(
                => $modalInstance.close()
                (res) =>
                  $scope.is_saving = false
                  $scope.error = res.data.error_message
              )
        ]
      });

      return inst

    ###
      # Shows the copy settings modal
      ###
    showCopySettings: ->

      #------------------------------
      # Get agent options
      #------------------------------

      # The list pane is open right now and has the list of agents we can use
      agents = @$scope.$parent?.ListCtrl?.agents
      if not agents then return false

      if agents.length == 1
        @showAlert('There are no other agents to copy settings from')
        return false

      # Dont include ourself in the list
      agents = agents.filter((x) => x.id != @agentId)

      #------------------------------
      # Function callback that loads and applies the settings
      #------------------------------

      copySettings = (settings) =>
        promise = @Api.sendDataGet({
          agent: "/agents/#{settings.agent_id}?extended=1"
          notif_prefs_table: "/agents/#{settings.agent_id}/notify-prefs/get-tables"
          teams: "/agent_teams"
          groups: "/agent_groups"
        }).then( (result) =>
          agent  = result.data.agent.agent
          teams  = result.data.teams.agent_teams
          groups = result.data.groups.groups

          agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table)
          notif_prefs = agentNotifPrefsModel.prefsTable

          agentFormModel = new EditAgentModel(agent, groups, teams)
          form = agentFormModel.form

          if settings.zones
            @form.zones.admin   = form.zones.admin
            @form.zones.reports = form.zones.reports || form.zones.admin

          if settings.teams
            tids = {}
            for team in form.teams
              tids[team.id] = team.value
            for team in @form.teams
              team.value = tids[team.id]

          if settings.groups
            gids = []
            for group in form.agent_groups
              if group.value then gids.push(group.id)
            for group in @form.agent_groups
              group.value = group.id in gids

          if settings.perms
            @perm_form = angular.copy result.data.agent.perm_overrides
            @deps_perms = @parseDepPermOverrides(agent.id, @ticketDeps, @chatDeps)

          if settings.ticket_notifs
            for n in ['sys_filters_email', 'sys_filters_alert', 'custom_filters_email', 'custom_filters_alert']
              if @notif_prefs.subs[n]? and notif_prefs.subs[n]?
                for r, rkey in  @notif_prefs.subs[n].rows
                  for c, ckey in r.cols
                    for subc, subckey in c
                      val = notif_prefs.subs[n]?.rows[rkey]?.cols[ckey]?[subckey]?.value || false
                      @notif_prefs.subs[n].rows[rkey].cols[ckey][subckey].value = val

          if settings.other_notifs
            for n in ['chat', 'task', 'twitter', 'feedback', 'publish', 'crm', 'account']
              if @notif_prefs.subs[n]? and notif_prefs.subs[n]?
                for r, rkey in  @notif_prefs.subs[n].rows
                  for subc, subckey in r.cols
                    val = notif_prefs.subs[n]?.rows[rkey]?.cols[subckey]?.value || false
                    @notif_prefs.subs[n].rows[rkey].cols[subckey].value = val
        )
        @$timeout(=> @updateHasPermOverridesStatus())
        return promise

      #------------------------------
      # Show the modal
      #------------------------------

      inst = @$modal.open({
        templateUrl: @getTemplatePath('Agents/copy-settings-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.agents = agents
          $scope.options = {
            agent_id: agents[0].id+"",
            zones: false,
            teams: false,
            groups: false,
            perms: false,
            ticket_notifs: false,
            other_notifs: false
          }

          $scope.doCopySettings = (settings) ->
            $scope.is_loading = true
            copySettings(settings).then(->
              $modalInstance.dismiss()
            )
        ]
      });


    ###
      # Shows the copy settings modal
      ###
    showLoginAs: ->
      agentName = @form.name
      agentId = @agentId
      Api = @Api

      inst = @$modal.open({
        templateUrl: @getTemplatePath('Agents/login-as-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.agentName = agentName
          $scope.is_loading = true

          Api.sendGet("/agents/#{agentId}/login-token").then( (res) ->
            $scope.is_loading = false
            $scope.login_token = res.data.login_token
          )
        ]
      });

      inst.result.then(=>

      )


    ###
      # Shows the copy settings modal
      ###
    showDelete: ->
      isSelf = @isSelf()

      deleteAgent = (settings) =>
        if settings.method == 'user'
          target = "/agents/#{@agentId}/delete/to-user"
        else
          target = "/agents/#{@agentId}/delete"

        p = @Api.sendDelete(target)
        p.then(=>
          # todo
          @service.agents.get(@agentId).then (agent) =>
            @service.agents._removeModel agent
          @$scope.$parent?.ListCtrl.deletedCount++
          @$state.go('agents.agents')
        )

        return p

      inst = @$modal.open({
        templateUrl: @getTemplatePath('Agents/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.options = {
            method: 'user'
          }

          $scope.isSelf = isSelf

          $scope.doDelete = (options) ->
            $scope.is_loading = true
            deleteAgent(options).then -> $modalInstance.dismiss()
        ]
      })

    ###
      # Shows the copy settings modal
      ###
    showEditProfile: ->
      @$modal.open({
        templateUrl: @getTemplatePath('Agents/edit-profile-modal.html'),
        controller: 'Admin_Agents_Ctrl_EditProfile',
        resolve: {
          agent: =>
            return @agent
          saveMethod: =>
            return (data, from) =>
              if from.new_image
                @agent.picture_blob = from.new_image
              else if from.form.picture_set == 'default'
                @agent.picture_blob = null

              @agent.timezone = from.form.timeone
              @agent.signature_html = from.form.signature_html

              if @agentId
                return @Api.sendPostJson("/agents/#{@agentId}/profile", data)
              else
                @pendingProfileData = data
        }
      })

    mergeDupePerson: (personId) ->
      merge = new window.parent.DeskPRO.Agent.Widget.Merge({
        tabType: 'person',
        metaId: @agentId,
        metaIdName: 'person_id',
        overlayUrl: DP_BASE_URL + 'agent/people/{id}/merge-overlay/{other}',
        mergeUrl: DP_BASE_URL + 'agent/people/{id}/merge/{other}',
        loadRoute: 'person:' + DP_BASE_URL + 'agent/people/{id}'
      })

      merge.openWithId(personId)

    ###
      # Returns an object hash of the complete form data
    ###
    getFormData: ->
      formData = {
        agent:              @agentFormModel.getFormData(),
        filter_subs:        @agentNotifPrefsModel.getFilterSubs(),
        other_subs:         @agentNotifPrefsModel.getOtherSubs(),
        perm_overrides:     @perm_form
        dep_perm_overrides: @deps_perms
      }

      if @pendingProfileData
        formData.profile = @pendingProfileData
        @pendingProfileData = null

      return formData

    saveAgent: ->
      if not @$scope.form_props.$valid
        return

      if @agentId
        return @doSaveAgent()
      else
        d = @$q.defer()

        @startSpinner('saving')

        @DpLicense.getLicInfo(true).then( (licInfo) =>
          @stopSpinner('saving', true)
          if licInfo.limits.remain_agents != 0
            @doSaveAgent().then(->
              d.resolve()
            , ->
              d.reject()
            )
          else
            @DpLicense.openUpgradeLicense('upgrade_plan').then(=>
              @doSaveAgent().then(->
                d.resolve()
              , ->
                d.reject()
              )
            )
        , =>
          @stopSpinner('saving', true)
          d.reject()
        )

        return d.promise

    ###
      # Saves the agent
    ###
    doSaveAgent: ->
      if not @$scope.form_props.$valid
        return

      @email_dupe_error = false
      @email_sysaccount_error = false
      @invalid_phone_error = false
      @startSpinner('saving')

      postData = @getFormData()

      if @agentId
        promise = @Api.sendPostJson("/agents/#{@agentId}", postData)
      else
        promise = @Api.sendPutJson("/agents", postData)

      promise.then( (res) =>
        @agent.display_name = @form.name
        @service.agents.mergeDataModel(@agent)

        if !@agentId
          @service.agents.all(true)
          @$state.go('agents.agents.edit', {id: res.data.person_id, created_agent: 1})

        @stopSpinner('saving')
      , (res) =>
        if res?.data?.error_code == 'dupe_email'
          @email_dupe_error = res.data.error_info.existing
        if res?.data?.error_code == 'system_email_addresses'
          @email_sysaccount_error = res.data.error_info.emails.join(', ')
        if res?.data?.error_code == 'invalid_phone_number'
          @invalid_phone_error = res.data.error_message + ': ' + res.data.error_info?.primary_phone
        if res?.data?.errors?.errors
          res.data.errors.errors.map (error) =>
            if 'agent.primary_phone.number' == error.prop
              @invalid_phone_error = error.message

        @stopSpinner('saving', true)
        @applyErrorResponseToView(res)
      )

      return promise



    isSelf: ->
      window.DP_PERSON_ID == @agentId



    parseDepPermOverrides: (agentId, ticketDeps, chatDeps) ->
      overrides =
        tickets: {}
        chat: {}

      for dep in ticketDeps
        assign = false
        full = false

        if agentId and dep.permissions?.users
          u = dep.permissions.users.filter((x) => x.id == agentId)[0]
          if u
            if u.name == 'full' then full = true else assign = true

        overrides.tickets[dep.id] = { assign: assign, full: full }

      for dep in chatDeps
        full = false
        if agentId and dep.permissions?.users
          u = dep.permissions.users.filter((x) => x.id == agentId)[0]
          if u
            full = true

        overrides.chat[dep.id] = { full: full }

      overrides


  Admin_Agents_Ctrl_Edit.EXPORT_CTRL()
