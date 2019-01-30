define [
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Strings'
], (
  Admin_Ctrl_Base,
  Strings
) ->
  class Admin_Main_Ctrl_HomeFrame extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Main_Ctrl_HomeFrame'
    @CTRL_AS   = 'Home'
    @DEPS      = ['$http', 'DpLicense', 'Growl']

    init: ->
      @$scope.iframe_src = window.ADMIN_DASH_IFRAME_SRC

  Admin_Main_Ctrl_HomeFrame.EXPORT_CTRL()
