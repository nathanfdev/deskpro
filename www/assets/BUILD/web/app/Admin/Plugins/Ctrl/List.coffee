define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Plugins_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Plugins_Ctrl_List'
    @CTRL_AS = 'ListCtrl'

    init: ->
      @packages = []

    initialLoad: ->
      promise = @Api.sendGet('/plugins/packages').then( (result) =>
        @packages = result.data.packages
      )
      return promise

  Admin_Plugins_Ctrl_List.EXPORT_CTRL()