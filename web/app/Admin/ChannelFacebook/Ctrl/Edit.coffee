define [
  'Admin/Main/Ctrl/Base',
  'Admin/ChannelFacebook/FormModel/EditFacebookPageModel',
  'DeskPRO/Util/Util'
], (
  Admin_Ctrl_Base,
  Admin_ChannelFacebook_FormModel_EditFacebookPageModel,
  Util
) ->
  class Admin_ChannelFacebook_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ChannelFacebook_Ctrl_Edit'
    @CTRL_AS = 'ChannelFacebookEdit'
    @DEPS    = ['Api', 'Growl', 'FacebookPagesData', '$stateParams', '$state', '$timeout']

    init: ->
      @pageId = parseInt(@$stateParams.id || 0)
      @page = null
      @form_model = null

    initialLoad: ->
      promise = @Api.sendGet("/channel/facebook/page/#{@pageId}").then((result) =>
        if result.data
          @page = result.data
          @form_model = new Admin_ChannelFacebook_FormModel_EditFacebookPageModel(@page || {})
          @setFormOnScope()
      )

      return promise

    setFormOnScope: ->
      @$scope.form = @form_model.form

    savePage: ->
      @startSpinner('saving_page')
      postData = { page: @form_model.getFormData() }
      @Api.sendPostJson("/channel/facebook/page/#{@pageId}", postData).then((result) =>
        @page = result.data
        @FacebookPagesData.updateModel(@page)
        @Growl.success(@getRegisteredMessage('saved_page'))
        @stopSpinner('saving_page')
        @$scope.$parent.ChannelFacebookList.pingElement('save_page')
      )

    connect: ->
      postData = @getPostData()
      promise = @Api.sendPostJson("/channel/facebook/connect_provider", postData)
      promise.then( (result) =>
        if result.data.success
          @$scope.connection_problem = false
          @page = result.data.account
          @form_model.setAccountData(result.data.account)
          if @pageId
            @FacebookPagesData.updateModel(@page)
          @ngApply()
          @Growl.success(@getRegisteredMessage('connected'))
        else
          @$scope.connection_problem = true
          @form_model.markConnected(false)
          @Growl.error(@getRegisteredMessage('connected_fail'))
        @stopSpinner('sms_connect_provider')
      )
      promise.error( (result) =>
        @$scope.connection_problem = true
        @form_model.markConnected(false)
        @stopSpinner('sms_connect_provider')
        @Growl.error(@getRegisteredMessage('connected_fail'))
      )
      @startSpinner('sms_connect_provider')
      return promise

    setupAndTest: ->
      postData = @getPostData()
      promise = @Api.sendPostJson("/channel/facebook/setup-and-test/twilio", postData)
      promise.then((result) =>
        if result
          checkTestStatus = =>
            url = "/channel/facebook/page/#{@pageId}"
            @$timeout =>
              @Api.sendGet(url).then((result) =>
                if result.data.is_tested
                  @stopSpinner('sms_test_provider')
                  @page = result.data
                  @form_model.setAccountData(result.data)
                  @FacebookPagesData.updateModel(@page)
                  @ngApply()
                  @Growl.success(@getRegisteredMessage('setup_and_tested_success'))
                else
                  checkTestStatus()
              )
            , 1000
          checkTestStatus()
        else
          @Growl.error(@getRegisteredMessage('connected_fail'))
          @form_model.markTested(false)
      )
      promise.error((result) =>
        @$scope.connection_problem = true
        @Growl.error(@getRegisteredMessage('connected_fail'))
        @stopSpinner('sms_test_provider')
      )
      @startSpinner('sms_test_provider')
      return promise



  Admin_ChannelFacebook_Ctrl_Edit.EXPORT_CTRL()
