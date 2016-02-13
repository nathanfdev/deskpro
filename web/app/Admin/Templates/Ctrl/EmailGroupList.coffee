define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Templates_Ctrl_EmailGroupList extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Templates_Ctrl_EmailGroupList'
    @CTRL_AS   = 'ListCtrl'
    @DEPS      = []

    initialLoad: ->
      promise = @Api.sendDataGet({
        info: '/email-templates-info'
      }).then( (res) =>
        @templateInfo = res.data.info.list
      )
      return promise

  Admin_Templates_Ctrl_EmailGroupList.EXPORT_CTRL()