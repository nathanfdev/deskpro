define ['Reports/Main/Ctrl/Base'], (Reports_Main_Ctrl_Bare) ->
  class Reports_Main_Ctrl_GoToAdmin extends Reports_Main_Ctrl_Bare
    @CTRL_ID   = 'Reports_Main_Ctrl_GoToAdmin'
    @DEPS      = ['$location']

    init: ->
      if not window.parent || not window.parent.DP_FRAME_OVERLAYS || not window.parent.DP_FRAME_OVERLAYS.admin
        window.location.href = window.DP_BASE_URL + 'admin/';
      else
        window.parent.DP_FRAME_OVERLAYS.admin.open()
        window.parent.DP_FRAME_OVERLAYS.reports.close()

  Reports_Main_Ctrl_GoToAdmin.EXPORT_CTRL()
