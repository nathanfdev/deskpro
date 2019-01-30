define ->
  class DeskPRO_Main_Service_InhelpState
    constructor: (Api) ->
      @Api = Api

      if window.DP_INHELP_STATES
        @states = window.DP_INHELP_STATES
      else
        @states = {}

    getState: (id) ->
      if not @states[id]?
        return null

      if @states[id] == 'open'
        return true

      return false

    setState: (id, state) ->

      if state
        state = 'open'
      else
        state = 'closed'

      if @states?[id] != state
        @Api.sendPost('/profile/inhelp/'+id+'/'+state)