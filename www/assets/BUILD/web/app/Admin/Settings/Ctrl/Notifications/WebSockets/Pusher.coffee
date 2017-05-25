define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Settings_Ctrl_Notifications_WebSockets_Pusher extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_Notifications_WebSockets_Pusher'
    @CTRL_AS   = 'Pusher'

    init: ->
      return

    initialLoad: ->
      return

  Admin_Settings_Ctrl_Notifications_WebSockets_Pusher.EXPORT_CTRL()