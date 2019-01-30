define ['DeskPRO/Util/Strings'], (Strings) ->
  class EditAgentModel
    constructor: (agent, groups, teams, primary_phone_number_region) ->
      @form = {}
      #--------------------
      # Basic props
      #--------------------

      @form.name = agent.name
      if agent.primary_phone
        @form.primary_phone = {
          region: agent.primary_phone.region || primary_phone_number_region
          number: agent.primary_phone.number || ''
          ext: agent.primary_phone.ext || ''
        }
      else
        @form.primary_phone = {
          region:  primary_phone_number_region
          number:  ''
          ext: ''
        }
      @form.primary_team = agent.primary_team
      @form.notification_settings = agent.notification_settings


      if agent.override_display_name
        @form.enable_display_name = true
        @form.override_name = agent.override_display_name
      else
        @form.enable_display_name = false
        @form.override_name = ''

      @form.zones = {
        admin: agent.can_admin,
        reports: agent.can_reports
      }

      #--------------------
      # Emails
      #--------------------

      @form.emails_list = []
      @form.email_primary = agent.primary_email?.email || ''

      if agent and agent.emails and agent.emails.length
        for email in agent.emails
          @form.emails_list.push(email.email)

      #--------------------
      # Teams
      #--------------------

      @form.teams = []
      for t in teams
        enabled = false
        for check in agent.teams
          if check.id == t.id
            enabled = true
            break

        @form.teams.push({
          id:    t.id,
          name:  t.name,
          value: enabled
        })

      #--------------------
      # Groups
      #--------------------

      @form.agent_groups = []
      for g in groups
        enabled = false
        for check in agent.usergroups
          if check.id == g.id
            enabled = true
            break
        if not enabled and not agent.id and g.sys_name == 'agent_all_perms'
          enabled = true

        @form.agent_groups.push({
          id:    g.id,
          title: g.title,
          value: enabled
        })

    getFormData: ->
      formData = {}
      formData.name = @form.name
      formData.primary_phone = {number: @form.primary_phone.number, ext: @form.primary_phone.ext}
      if not formData.primary_phone.number
        formData.primary_phone = null
      formData.notification_settings = @form.notification_settings
      formData.primary_team = if @form.primary_team then @form.primary_team.id else null

      if @form.enable_display_name and Strings.trim(@form.override_name)
        formData.override_name = Strings.trim(@form.override_name)
      else
        formData.override_name = ''

      formData.emails = @form.emails_list
      primary_email = @form.email_primary

      # the primary email goes first
      formData.emails.sort( (a, b) ->
        if a == primary_email then return -1
        if b == primary_email then return 1
        return 0
      )

      formData.zones = []
      if @form.zones.admin   then formData.zones.push('admin')
      if @form.zones.reports then formData.zones.push('reports')

      formData.teams = []
      for t in @form.teams
        if t.value
          formData.teams.push(t.id)

      formData.agent_groups = []
      for g in @form.agent_groups
        if g.value
          formData.agent_groups.push(g.id)

      return formData
