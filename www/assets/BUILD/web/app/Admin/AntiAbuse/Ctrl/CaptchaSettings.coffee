define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_AntiAbuse_Ctrl_CaptchaSettings extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_AntiAbuse_Ctrl_CaptchaSettings'
    @CTRL_AS = 'CaptchaSettings'

    _url = '/settings/anti_abuse/captcha'

    init: ->
      @$scope.settings = null
      @$scope.general_settings = null

    initialLoad: ->
      captchaPromise = @Api2.sendGet(_url).then (res) =>
        @$scope.settings = res.data.data
      generalPromise = @Api.sendGet('/general_settings').then (res) =>
        @$scope.general_settings = res.data.general_settings

      return @$q.all([captchaPromise, generalPromise])

    save: ->
      captchaPromise = @Api2.sendPutJson(_url, @$scope.settings) 
      generalPromise = @Api.sendPostJson('/general_settings', {
        general_settings: @$scope.general_settings
      })
      @startSpinner('saving')
      @$q.all([captchaPromise, generalPromise]).then( =>
        @stopSpinner('saving')
        @Growl.success @getRegisteredMessage('saved_settings')
      , (info) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

  Admin_AntiAbuse_Ctrl_CaptchaSettings.EXPORT_CTRL()
