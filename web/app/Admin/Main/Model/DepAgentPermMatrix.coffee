define ->
  class Admin_Main_Model_DepAgentPermMatrix
    constructor: ->
      @agents = []
      @groups = []

      @agents_map = {}
      @groups_map = {}


    ###*
    * Add a group
      *
      * @param {Model}  group        The group model
      * @param {Array}  perms_array  Array of current perm values
    ###
    addGroup: (group, perms_array) ->

      perms = {}
      for p in perms_array
        perms[p.name] = { state: false, set_state: p.state, soft_state: false, locked: false }

      obj = {
        type:  'group',
        model: group,
        perms: perms,
        aids:  []
      }

      @groups.push(obj)
      @groups_map[group.id] = obj


    ###*
    * Add an agnet
      *
      * @param {Model}  agent        The agent model
      * @param {Array}  perms_array  Array of current perm values
    ###
    addAgent: (agent, perms_array) ->

      perms = {}
      for p in perms_array
        perms[p.name] = { state: false, set_state: p.state, soft_state: true, locked: false }

      obj = {
        type:  'agent',
        model: agent,
        perms: perms
      }

      @agents.push(obj)
      @agents_map[agent.id] = obj



    ###*
    * After all perm values are added to the matrix,
      * this should be called to propogate values from ug's to
      * agents and set the proper locked state.
    ###
    initPerms: (group_perms, agent_perms) ->

      if group_perms
        for p in group_perms
          if not @groups_map[p.usergroup_id]? then continue
          if not @groups_map[p.usergroup_id].perms[p.perm_name]?
            @groups_map[p.usergroup_id].perms[p.perm_name] = { state: false, set_state: false, soft_state: false, locked: false }

          @groups_map[p.usergroup_id].perms[p.perm_name].set_state = true

      if agent_perms
        for p in agent_perms
          if not @agents_map[p.agent_id]? then continue
          if not @agents_map[p.agent_id].perms[p.perm_name]?
            @agents_map[p.agent_id].perms[p.perm_name] = { state: false, set_state: false, soft_state: false, locked: false }

          @agents_map[p.agent_id].perms[p.perm_name].set_state = true

      # Init group_to_agents map
      # And fill/correct missing perms
      for agentObj in @agents
        agent = agentObj.model
        agentPerms = agentObj.perms

        if not agent.agentgroup_ids then continue

        for gid in agent.agentgroup_ids
          if not @groups_map[gid] then continue
          @groups_map[gid].aids.push(agent.id)

        if not agentPerms.full?   then agentPerms.full   = { state: false, set_state: false, soft_state: false, locked: false }
        if not agentPerms.assign? then agentPerms.assign = { state: false, set_state: false, soft_state: false, locked: false }

        if agentPerms.full.set_state or agentPerms.full.soft_state
          agentPerms.full.state = true
        else
          agentPerms.full.state = false

        if agentPerms.full.state
          agentPerms.assign.soft_state  = true
          agentPerms.assign.locked      = true

        if agentPerms.assign.set_state or agentPerms.assign.soft_state
          agentPerms.assign.state = true
        else
          agentPerms.assign.state = false

      # Fill/correct usergroup perms
      # And propogate values to agents
      for groupObj in @groups
        group = groupObj.model
        groupPerms = groupObj.perms

        if not groupPerms.full?   then groupPerms.full   = { state: false, set_state: false, soft_state: false, locked: false }
        if not groupPerms.assign? then groupPerms.assign = { state: false, set_state: false, soft_state: false, locked: false }

        if groupPerms.full.set_state or groupPerms.full.soft_state
          groupPerms.full.state = true
        else
          groupPerms.full.state = false

        if groupPerms.full.state
          groupPerms.assign.soft_state  = true
          groupPerms.assign.locked      = true

        if groupPerms.assign.set_state or groupPerms.assign.soft_state
          groupPerms.assign.state = true
        else
          groupPerms.assign.state = false

        if groupPerms.full.state or groupPerms.assign.state
          for aid in groupObj.aids
            if groupPerms.full.state
              @agents_map[aid].perms.full.soft_state   = true
              @agents_map[aid].perms.full.state        = true
              @agents_map[aid].perms.full.locked       = true
              @agents_map[aid].perms.assign.soft_state = true
              @agents_map[aid].perms.assign.state      = true
              @agents_map[aid].perms.assign.locked     = true
            else if groupPerms.assign
              @agents_map[aid].perms.assign.soft_state  = true
              @agents_map[aid].perms.assign.state       = true
              @agents_map[aid].perms.assign.locked      = true


    ###*
    * Refreshes permissions on agents based on current group permssions
    ###
    refreshAgentGroupPerms: (aid = null) ->

      if aid
        agents = [@agents_map[aid]]
      else
        agents = @agents

      for agentObj in agents
        agent = agentObj.model
        agentPerms = agentObj.perms

        # Reset soft/locked state
        agentPerms.full.soft_state   = false
        agentPerms.full.locked       = false
        agentPerms.assign.soft_state = false
        agentPerms.assign.locked     = false

        # Then process usergroups on them
        for gid in agent.agentgroup_ids
          groupPerms = @groups_map[gid].perms

          if groupPerms.full.state
            agentPerms.full.soft_state   = true
            agentPerms.full.locked       = true
            agentPerms.assign.soft_state = true
            agentPerms.assign.locked     = true
          else if groupPerms.assign.state
            agentPerms.assign.soft_state = true
            agentPerms.assign.locked     = true

        # Now set the model state used in the template
        if agentPerms.full.set_state or agentPerms.full.soft_state
          agentPerms.full.state = true

        if agentPerms.full.state
          agentPerms.assign.soft_state  = true
          agentPerms.assign.locked      = true

        if agentPerms.assign.set_state or agentPerms.assign.soft_state
          agentPerms.assign.state = true


    ###*
    * Takes the value of a permission
      *
      * @param {permission}  perm    The permission to resolve
    ###
    setGroupPerm: (gid, name, value) ->
      groupPerms = @groups_map[gid].perms

      if value == '&'
        value = groupPerms[name].state

      groupPerms[name].set_state = value

      if name == 'full'
        groupPerms.full.state      = value
        groupPerms.full.soft_state = false
        groupPerms.full.locked     = false

        if value
          groupPerms.assign.state = true
          groupPerms.assign.soft_state = true
          groupPerms.assign.locked = true
        else
          groupPerms.assign.soft_state = false
          groupPerms.assign.locked = false

          if groupPerms.assign.set_state or groupPerms.assign.soft_state
            groupPerms.assign.state = true
          else
            groupPerms.assign.state = false
      else
        if groupPerms[name].set_state or groupPerms[name].soft_state
          groupPerms[name].state = true
        else
          groupPerms[name].state = false

      @refreshAgentGroupPerms()


    ###*
    * Takes the value of a permission
      *
      * @param {permission}  perm    The permission to resolve
    ###
    setAgentPerm: (aid, name, value) ->
      agentPerms = @agents_map[aid].perms

      if value == '&'
        value = agentPerms[name].state

      agentPerms[name].set_state = value

      if name == 'full'
        agentPerms.full.state      = value
        agentPerms.full.soft_state = false
        agentPerms.full.locked     = false

        if value
          agentPerms.assign.soft_state = true
          agentPerms.assign.locked = true
        else
          agentPerms.assign.soft_state = false
          agentPerms.assign.locked = false

          if agentPerms.assign.set_state or agentPerms.assign.soft_state
            agentPerms.assign.state = true
          else
            agentPerms.assign.state = false
      else
        if agentPerms[name].set_state or agentPerms[name].soft_state
          agentPerms[name].state = true
        else
          agentPerms[name].state = false

      @refreshAgentGroupPerms(aid)


    ###
      # Get all permission data as an array
      #
      # @return {Array}
    ###
    getPermsData: ->
      perms = []

      for agentObj in @agents
        if agentObj.perms.full.state
          perms.push({
            person_id: agentObj.model.id,
            name: 'full',
            value: 1
          })
        else if agentObj.perms.assign.state
          perms.push({
            person_id: agentObj.model.id,
            name: 'assign',
            value: 1
          })

      for groupObj in @groups
        if groupObj.perms.full.state
          perms.push({
            usergroup_id: groupObj.model.id,
            name: 'full',
            value: 1
          })
        else if groupObj.perms.assign.state
          perms.push({
            usergroup_id: groupObj.model.id,
            name: 'assign',
            value: 1
          })

      return perms