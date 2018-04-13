define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Templates_Ctrl_EmailGroupListOld extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Templates_Ctrl_EmailGroupListOld'
    @CTRL_AS   = 'ListCtrl'
    @DEPS      = []

    initialLoad: ->
      promise = @Api.sendDataGet({
        info: '/email-templates-info'
      }).then( (res) =>
        @templateInfo = res.data.info.list
        console.log @templateInfo
      )
      return promise

  Admin_Templates_Ctrl_EmailGroupListOld.EXPORT_CTRL()