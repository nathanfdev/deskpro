define [
  'Admin/Main/Ctrl/Base',
  'Admin/TicketAccounts/FormModel/EditTicketAccountModel',
  'underscore'
], (
  Admin_Ctrl_Base,
  EditTicketAccountModel,
  _
) ->
  class Admin_TicketAccounts_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketAccounts_Ctrl_Edit'
    @CTRL_AS = 'TicketAccountsEdit'
    @DEPS    = ['Api', 'Growl', 'TicketAccountsData', '$stateParams', '$modal', 'dpObTypesDefTicketActions', '$location', '$upload', '$http', 'Api2']

    init: ->
      @actionsTypeDef = @dpObTypesDefTicketActions
      @$scope.actionOptionTypes = []
      @$scope.actions_form = {}
      @$scope.message_count = null

      @accountId = parseInt(@$stateParams.id || 0)
      @didPassTest = false
      @testMessageCount = 0
      @didConfirmExistingMessages = false
      @test_email = {
        to: window.DP_PERSON_EMAIL,
        from: '',
        subject: 'Test email',
        message: 'This is a test. If you see this email in your inbox, your outgoing email account settings are correct.'
      }
      @$scope.path = @$location.path()
      @deferredData = {}

    updateCriteriaOptionTypes: ->
      types = ['web', 'web.user']
      setActionOptions = @actionsTypeDef.getOptionsForTypes(types, { dynamicOptions: @customActions })
      @$scope.actionOptionTypes.length = 0
      for opt in setActionOptions
        @$scope.actionOptionTypes.push(opt)

    getFormModel: ->
      return new EditTicketAccountModel(@account || {}, @deps || [], @trigger || {}, @brands || {})

    initialLoad: ->
      dep_promise = @DataService.get('TicketDeps').loadList().then( (list) =>
        @deps = list
      )

      brands_promise = @Api2.sendGet('brands').then( (res) =>
        @brands = res.data.data
      )

      get = {
        customActions: '/ticket_triggers/get-custom-actions'
      }
      if @accountId
        get.trigger = "/ticket_triggers/email_accounts/#{@accountId}"
      else
        get.trigger = "/ticket_triggers/newticket"

      trigger_promise = @Api.sendDataGet(get).then( (result) =>
        @customActions = result.data.customActions.action_defs

        if result.data && result.data.trigger
          if result.data.trigger.trigger
            @trigger = result.data.trigger.trigger
            @triggerId = @trigger.id

            if @trigger.actions?.actions?.length
              @$scope.actions_form = {}
              for action in @trigger.actions.actions
                rowId = _.uniqueId('action')
                @$scope.actions_form[rowId] = action
          else
            @trigger = result.data.trigger
            @triggerId = 0
        else
          @trigger = {}
          @triggerId = 0
      )

      trigger_data_promise = @actionsTypeDef.loadDataOptions()

      proms = [trigger_promise, trigger_data_promise, dep_promise, brands_promise]

      if not @accountId
        @account = {is_enabled: true, is_all_brands: true}
        @trigger = {}
      else
        data_promise = @Api.sendDataGet({
          'email_account': '/email_accounts/' + @accountId
        }).then( (result) =>
          @account = result.data.email_account.email_account
          @trigger = result.data.email_account.trigger
        )

        proms.push(data_promise)

      final_promise = @$q.all(proms)

      final_promise.then(=>
        @form_model = @getFormModel()
        if @deferredData.gmail?
          for type in ['in', 'out']
            for v in ['clientId', 'clientSecret', 'token', 'refreshToken']
              @form_model.form[type + '_gmail_account'][v] = @deferredData.gmail[v]

        @$scope.form = @form_model.form

        if not @accountId
          @$scope.form.incoming_type = ''
          @$scope.form.outgoing_type = 'smtp'

        if not @$scope.form.outgoing_type
          @$scope.form.outgoing_type = 'php_mail'

        @updateCriteriaOptionTypes()

        if @$scope.form.encryption_enabled
          @$scope.show_adv = true
      )
      return final_promise


    ###
    # Saves the current form
    #
    # @return {promise}
    ###
    saveAccount: ->
      return if @$scope.form_props.$invalid

      if not @account.id and not @new_is_confirmed and @$scope.form.account_type != 'outgoing'
        @showNewAccountConfirm()
        return

      postData = @form_model.getFormData()

      triggerSaver = =>
        postData = {
          actions:       []
        }
        if @$scope.actions_form
          for own _x, act of @$scope.actions_form
            if act.type
              postData.actions.push(act)
        @Api.sendPostJson('/ticket_triggers/email_accounts/' + @account.id, postData)

      @startSpinner('saving_account')
      if @account.id
        is_new = false
        promise = @Api.sendPostJson('/email_accounts/' + @account.id, postData)
      else
        is_new = true
        promise = @Api.sendPutJson('/email_accounts', postData)

      promise.success( (result) =>
        @account.id = result.email_account_id || @account.id
        @account.is_enabled = @$scope.form.is_enabled
        triggerSaver().then(=>
          @stopSpinner('saving_account', true).then(=>
            @Growl.success(@getRegisteredMessage('saved_account'))
          )
          @form_model.apply()
          @TicketAccountsData.updateModel(@account)
          @uploadFiles().then(=>
              @skipDirtyState()
              if is_new
                @$state.go('emails.ticket_accounts.gocreate')
              else
                @$state.go('emails.ticket_accounts')
            , (err) =>
              @Growl.error(err)
          )
        )
      )
      promise.error( (info, code) =>
        @stopSpinner('saving_account', true)
        if info?.error_code == 'invalid_data' and info?.error_message
          @showAlert(info.error_message)
        else
          @applyErrorResponseToView(info)
      )

      return promise


    ###
    # Test current account settings
    #
    # @return {promise}
    ###
    loadAccountTest: ->
      return @Api.sendPostJson('/email_accounts/test-account', @form_model.getFormData()).success( (result) =>
        @didPassTest = result.is_success
      )


    ###
    # Test current outgoing settings with message details from @test_email object.
    #
    # @return {promise}
    ###
    loadOutgoingAccountTest: ->
      form_data = @form_model.getFormData()
      form_data.test_email = @test_email

      return @Api.sendPostJson('/email_accounts/test-outgoing-account', form_data, null, { timeout: 12000})


    ###
    # Show the test account modal
    ###
    testAccountModal: ->
      me = @
      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketAccounts/test-account-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) =>
          $scope.dismiss = =>
            $modalInstance.dismiss();

          $scope.showLog = =>
            $scope.showing_log = true

          testNow = =>
            $scope.showing_log = false
            $scope.is_testing = true
            @loadAccountTest().success( (result) =>
              $scope.is_testing    = false
              $scope.is_success    = result.is_success
              $scope.log           = result.log
              $scope.message_count = result.message_count
              me.$scope.message_count = result.message_count
            ).error(=>
              $scope.showing_log   = true
              $scope.is_testing    = false
              $scope.is_success    = false
              $scope.log           = "Server Error"
              $scope.message_count = 0
              me.$scope.message_count = null
            )

          testNow();

          $scope.testNow = ->
            testNow()
        ]
      });

    showNewAccountConfirm: ->
      me = @
      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketAccounts/new-account-confirm.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) =>

          $scope.message_count = me.$scope.message_count

          $scope.dismiss = ->
            $modalInstance.dismiss();

          $scope.confirm = ->
            $modalInstance.close(true);
        ]
      })

      inst.result.then( (r) =>
        if r
          @new_is_confirmed = true
          @saveAccount()
      )

    setupTestModalScope: ($scope) ->
      return

    ###
      # Show the test account modal
    ###
    testOutgoingModal: ->
      me = @
      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketAccounts/test-outgoing-modal.html'),
        resolve: {
          test_email: =>
            @test_email.from = @form_model.form.address
            return @test_email
        },
        controller: ['$scope', '$modalInstance', 'test_email', ($scope, $modalInstance, test_email) =>
          $scope.dismiss = =>
            $modalInstance.dismiss();

          $scope.showLog = =>
            $scope.showing_log = true

          $scope.test_email = test_email
          me.setupTestModalScope($scope)

          testNow = =>
            $scope.testing_started = true
            $scope.showing_log = false
            $scope.is_testing = true

            if not test_email.from
              test_email.from = me.form_model.form.address

            me.loadOutgoingAccountTest().success( (result) =>
              $scope.is_testing    = false
              $scope.is_success    = result.is_success
              $scope.log           = result.log
              $scope.message_count = result.message_count
            ).error( (result) =>
              if result.status == 0 or result.status == 524
                $scope.showing_log   = true
                $scope.is_testing    = false
                $scope.is_success    = false
                $scope.log           = "The test failed due to a network problem. For example, the test may have timed out due to a firewall blocking it."
                $scope.message_count = 0
              else
                $scope.showing_log   = true
                $scope.is_testing    = false
                $scope.is_success    = false
                $scope.log           = "Server Error"
                $scope.message_count = 0
            )

          resetTest = =>
            $scope.testing_started = false

          $scope.testNow = ->
            testNow()
          $scope.resetTest = ->
            resetTest()
        ]
      });

    handleBrand: (brandId, e) ->
      index = @form_model.form.brands.indexOf brandId
      if index == -1
        @form_model.form.brands.unshift brandId
      else
        if (@form_model.form.brands.length > 1)
          @form_model.form.brands.splice(index, 1)
        else
          alert "Account needs to be linked to at least one Brand"
          $(e.target).prop("checked", true)
          return true

    getCode: (url) ->
      newWindow = window.open(url, 'name', 'height=600,width=450');
      if window.focus then newWindow.focus()



    getAccessToken: (url, type) =>
      if !(@$scope.form["#{type}_gmail_account"].code || '').length then return
      url = url + '?code=' + encodeURIComponent(@$scope.form["#{type}_gmail_account"].code)
      @$http({method: 'GET', url: url }).then (res) =>
        if res.data?.error
          @Growl.error(res.data.error)
        else
          @$scope.form["#{type}_gmail_account"].token = res.data.access_token
          @$scope.form["#{type}_gmail_account"].refreshToken = res.data.refresh_token


    onFileSelect: (files, type) ->
      if (!@$scope.files)
        @$scope.files = {}
      @$scope.files[type] = files[0]
      if (type == 'certificate')
        @$scope.form.cert_file = files[0].name
      else if (type == 'key')
        @$scope.form.key_file = files[0].name

    uploadFiles: () ->
      return new Promise( (resolve, reject) =>
        if (!@$scope.files || (!@$scope.files.certificate && !@$scope.files.key))
          return resolve()
        if (!@$scope.files.certificate || !@$scope.files.key)
          return reject('You must add a certificate and a key')
        @$upload.upload({
          url: @Api2.formatUrl('/email_accounts/'+@form_model.account.id+'/encryption'),
          data:{ cert: @$scope.files.certificate, key: @$scope.files.key, pass_phrase: @form_model.form.key_pass_phrase }
        }).success( (data) =>
          @setCertificate data.data.cert_blob
          @setKey data.data.key_blob
          return resolve()
        ).error( (data) =>
          return reject(data?.error_message || 'Error')
        )
      )

    setCertificate: (blob) =>
      if !blob?
        @$scope.form.cert_file = null
      else
        @$scope.form.cert_file = blob.filename

    setKey: (blob) =>
      if !blob?
        @$scope.form.key_file = null
      else
        @$scope.form.key_file = blob.filename

    deleteCertificate: () =>
      if @form_model.account.cert_blob
        @Api2.sendDelete('/email_accounts/'+@form_model.account.id+'/certificate').success(() =>
          @$scope.form.cert_file = null
        )
      else
        @$scope.files.certificate = null
        @$scope.form.cert_file = null


    deleteKey: () =>
      if @form_model.account.cert_blob
        @Api2.sendDelete('/email_accounts/'+@form_model.account.id+'/key').success(() =>
          @$scope.form.key_file = null
        )
      else
        @$scope.files.key = null
        @$scope.form.key_file = null




  Admin_TicketAccounts_Ctrl_Edit.EXPORT_CTRL()
