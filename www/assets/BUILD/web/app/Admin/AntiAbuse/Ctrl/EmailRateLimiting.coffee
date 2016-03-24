define [
 'Admin/Main/Ctrl/Base'
 'angular'
], (
 Admin_Ctrl_Base
 angular
) ->
 class Admin_AntiAbuse_Ctrl_EmailRateLimiting extends Admin_Ctrl_Base
  @CTRL_ID = 'Admin_AntiAbuse_Ctrl_EmailRateLimiting'
  @CTRL_AS = 'EmailRateLimiting'

 Admin_AntiAbuse_Ctrl_EmailRateLimiting.EXPORT_CTRL()
