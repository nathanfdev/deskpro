define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Plugins_Ctrl_Install extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Plugins_Ctrl_Install'
    @CTRL_AS = 'InstallCtrl'

    init: ->
      @package = []

    initialLoad: ->
      promise = @Api.sendGet("/plugins/package/#{@$stateParams.name}/installer").then( (result) =>
        @package = result.data.plugin_def
      )
      return promise

  Admin_Plugins_Ctrl_Install.EXPORT_CTRL()