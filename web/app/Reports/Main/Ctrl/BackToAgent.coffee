define ['Reports/Main/Ctrl/Base'], (Reports_Main_Ctrl_Bare) ->
  class Reports_Main_Ctrl_BackToAgent extends Reports_Main_Ctrl_Bare
    @CTRL_ID   = 'Reports_Main_Ctrl_BackToAgent'
    @DEPS      = ['$location']

    init: ->
      # Redirect back to agent
      # Unless this is in an iframe, in which case simply viewing
      # this route will cause the parent to close the iframe and
      # make the agent interface visible again
      if not window.parent || not window.parent.DP_FRAME_OVERLAYS || not window.parent.DP_FRAME_OVERLAYS.reports
        window.location.href = window.DP_BASE_URL + 'agent/';
      else
        window.parent.DP_FRAME_OVERLAYS.reports.close()

  Reports_Main_Ctrl_BackToAgent.EXPORT_CTRL()
