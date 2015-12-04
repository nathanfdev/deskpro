define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketProblems_Ctrl_Settings extends Admin_Ctrl_Base

    @CTRL_ID = 'Admin_TicketProblems_Ctrl_Settings'
    @CTRL_AS = 'Settings'
    @DEPS = []

    init: ->
      @map = {} # groups map
      @service = @DataService.get('Problems')
      @$scope.settings = null
      @$scope.updateAgents = @updateAgents
      @$scope.perm = {selected: 'view'}

      @$scope.$watch('settings.enabled', (newVal, oldVal) =>
        @$scope.updateAgents() if parseInt(newVal)
      )


    initialLoad: ->
      @service.load().then (settings) =>
        @$scope.settings = settings
        settings.groups.map (group) => @map[group.id] = group
        settings.groups.sort (a, b) =>
          return 1 if b.sys_name == 'agent_all_perms'
          return -1 if a.sys_name == 'agent_all_perms'
          return 1 if b.sys_name == 'agent_all_safe_perms'
          return -1 if a.sys_name == 'agent_all_safe_perms'
          a = (a.sys_name || a.title).toLowerCase()
          b = (b.sys_name || b.title).toLowerCase()
          return a.localeCompare(b)



    # update agents checkboxes states
    updateAgents: =>

      perm = @$scope.perm.selected
      @$scope.settings.agents.map (agent) =>

        for group in agent.usergroups

          if @map[group.id]?.perms.problems[perm]
            agent[perm + '_checked'] = true
            agent[perm + '_disabled'] = true
            return

        agent[perm + '_checked'] = agent.perms.problems[perm]
        agent[perm + '_disabled'] = false



    save: ->
      @startSpinner('saving')

      perm = @$scope.perm.selected
      @$scope.settings.agents.map (agent) ->
        return if agent[perm + '_disabled']
        agent.perms.problems[perm] = agent[perm + '_checked']

      @service.save().then(
        =>
          @stopSpinner('saving')
        =>
          @stopSpinner('saving')
      )





  Admin_TicketProblems_Ctrl_Settings.EXPORT_CTRL()