define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
    @CTRL_AS = 'Portal'

    init: ->
      @portal_enabled = false
      return

    initialLoad: ->

  Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()