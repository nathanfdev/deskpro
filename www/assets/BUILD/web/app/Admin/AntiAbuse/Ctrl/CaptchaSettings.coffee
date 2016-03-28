define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_AntiAbuse_Ctrl_CaptchaSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_AntiAbuse_Ctrl_CaptchaSettings'
    @CTRL_AS = 'CaptchaSettings'

    _url = '/settings/anti_abuse/captcha'

    init: ->
      @$scope.settings = null

    initialLoad: ->
      @Api2.sendGet(_url).then (res) =>
        @$scope.settings = res.data.data

    save: ->
      @startSpinner('saving')
      @Api2.sendPutJson(_url, @$scope.settings).success( =>
        @stopSpinner('saving')
        @Growl.success @getRegisteredMessage('saved_settings')
      ).error( (info) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_AntiAbuse_Ctrl_CaptchaSettings.EXPORT_CTRL()
