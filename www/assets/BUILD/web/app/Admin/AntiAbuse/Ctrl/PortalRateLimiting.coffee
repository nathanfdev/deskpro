define [
  'Admin/Main/Ctrl/Base'
  'angular'
], (
  Admin_Ctrl_Base
  angular
) ->
  class Admin_AntiAbuse_Ctrl_PortalRateLimiting extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_AntiAbuse_Ctrl_PortalRateLimiting'
    @CTRL_AS = 'PortalRateLimiting'

  Admin_AntiAbuse_Ctrl_PortalRateLimiting.EXPORT_CTRL()
