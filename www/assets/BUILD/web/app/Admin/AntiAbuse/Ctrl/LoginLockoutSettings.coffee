define [
  'Admin/Main/Ctrl/Base'
  'angular'
], (
  Admin_Ctrl_Base
  angular
) ->
  class Admin_AntiAbuse_Ctrl_LoginLockoutSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_AntiAbuse_Ctrl_LoginLockoutSettings'
    @CTRL_AS = 'LoginLockoutSettings'

  Admin_AntiAbuse_Ctrl_LoginLockoutSettings.EXPORT_CTRL()
