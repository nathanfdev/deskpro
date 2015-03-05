define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Agents_Ctrl_DeletedList extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Agents_Ctrl_DeletedList'
    @CTRL_AS   = 'ListCtrl'
    @DEPS      = []

    init: ->
      return

    initialLoad: ->
      promise = @Api.sendGet('/agents/deleted').then( (result) =>
        @agents = result.data.agents
      )
      return promise

    removeAgentFromList: (id) ->
      @agents = @agents.filter((x) -> x.id != id)

    updateAgent: (agent) ->
      for a in @agents
        if a.id == agent.id
          a.display_name = agent.display_name

    addAgent: (id, name) ->
      @agents.push({
        id: id,
        display_name: name
      })

  Admin_Agents_Ctrl_DeletedList.EXPORT_CTRL()