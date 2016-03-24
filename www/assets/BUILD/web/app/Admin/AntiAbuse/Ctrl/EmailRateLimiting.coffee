define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
 class Admin_AntiAbuse_Ctrl_EmailRateLimiting extends Admin_Ctrl_Base
  @CTRL_ID = 'Admin_AntiAbuse_Ctrl_EmailRateLimiting'
  @CTRL_AS = 'EmailRateLimiting'

  _url = '/email_accounts/settings'

  init: ->
   @$scope.settings = null

  initialLoad: ->
   @Api.sendGet(_url).then (res) =>
    @$scope.settings = res.data.email_settings

  save: ->
   postData = {
    settings: @$scope.settings
   }

   @startSpinner('saving')
   @Api.sendPutJson(_url, postData).success( =>
    @stopSpinner('saving')
    @Growl.success @getRegisteredMessage('saved_settings')
   ).error( (info) =>
    @stopSpinner('saving', true)
    @applyErrorResponseToView(info)
   )

 Admin_AntiAbuse_Ctrl_EmailRateLimiting.EXPORT_CTRL()
