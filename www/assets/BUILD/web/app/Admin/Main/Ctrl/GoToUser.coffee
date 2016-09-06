define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Main_Ctrl_GoToUser extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Main_Ctrl_GoToUser'

    init: ->
      if not window.parent || not window.parent.DP_FRAME_OVERLAYS || not window.parent.DP_FRAME_OVERLAYS.reports
        window.location.href = window.DP_BASE_URL;
      else
        window.parent.DP_FRAME_OVERLAYS.user.open()
        window.parent.DP_FRAME_OVERLAYS.admin.close()

  Admin_Main_Ctrl_GoToUser.EXPORT_CTRL()