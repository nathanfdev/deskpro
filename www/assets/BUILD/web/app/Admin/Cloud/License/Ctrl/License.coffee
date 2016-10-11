define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Cloud_License_Ctrl_License extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Cloud_License_Ctrl_License'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$window']

    init: ->
      @$scope.iframe_loading = true

    initialLoad: ->
      @Api.sendGet('/dp_license/cloud/billing-login-token').then( (result) =>
        @$scope.iframe_loading = false
        @$scope.iframe_code    = '<iframe src="' + result.data.ma_url + '" frameborder="0"></iframe>'
      )
      return null

  Admin_Cloud_License_Ctrl_License.EXPORT_CTRL()