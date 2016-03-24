define [
  'Admin/Main/Ctrl/Base'
  'angular'
], (
  Admin_Ctrl_Base
  angular
) ->
  class Admin_AntiAbuse_Ctrl_CaptchaSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_AntiAbuse_Ctrl_CaptchaSettings'
    @CTRL_AS = 'CaptchaSettings'

  Admin_AntiAbuse_Ctrl_CaptchaSettings.EXPORT_CTRL()
