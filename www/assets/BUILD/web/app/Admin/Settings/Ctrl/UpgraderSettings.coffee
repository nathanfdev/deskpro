define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base) ->
  class Admin_Settings_Ctrl_UpdaterSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Settings_Ctrl_UpdaterSettings'
    @CTRL_AS = 'Settings'
    @DEPS = []

    init: ->
      @settings = null

    initialLoad: ->
      return null

  Admin_Settings_Ctrl_UpdaterSettings.EXPORT_CTRL()
