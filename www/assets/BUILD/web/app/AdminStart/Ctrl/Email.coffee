define ['AdminStart/Ctrl/StartBase', 'Admin/TicketAccounts/FormModel/EditTicketAccountModel'], (StartBase, EditTicketAccountModel) ->
  class AdminStart_Ctrl_Email extends StartBase
    @CTRL_ID = 'AdminStart_Ctrl_Email'
    @DEPS    = ['$modal', '$location']

    init: ->
      @$scope.TicketAccountsEdit = this
      @test_email = {
        to: '',
        from: '',
        subject: 'Test email',
        message: 'This is a test. If you see this email in your inbox, your outgoing email account settings are correct.'
      }

      @account = {
        email_address: '',
        connection_type: '',
        other_addresses: [],
        in_pop3_account: {},
        in_imap_account: {},
        in_gmial_account: {},
        linked_transport: {
          transport_type: '',
          transport_options: {}
        }
      }

      @form_model = new EditTicketAccountModel(@account, [], {})
      @form_model.form.incoming_type = ''
      @form_model.form.outgoing_type = 'php_mail'
      @$scope.form = @form_model.form

    ###
    # Saves the current form
    #
    # @return {promise}
    ###
    saveAndContinue: ->
      @$scope.email_is_error = null
      postData = @form_model.getFormData()
      postData.is_enabled = true

      if not postData.address or postData.address.length < 3 or postData.address.indexOf('@') == -1
        @$scope.email_is_error = 'invalid_email'
        return

      @$scope.is_loading = true
      promise = @Api.sendPostJson('/email_accounts', postData)

      promise.success( (result) =>
        @account.id = result.email_account_id || @account.id
        @account.is_enabled = true
        @$location.path('/finish')
      )
      promise.error( (info, code) =>
        @$scope.is_loading = false
        @$scope.email_is_error = 'general'
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

      return @Api.sendPostJson('/email_accounts/test-outgoing-account', form_data)


    ###
      # Show the test account modal
    ###
    testAccountModal: ->
      inst = @$modal.open({
        templateUrl: 'AdminInterface/TicketAccounts/test-account-modal.html',
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
            ).error(=>
              $scope.showing_log   = true
              $scope.is_testing    = false
              $scope.is_success    = false
              $scope.log           = "Server Error"
              $scope.message_count = 0
            )

          testNow();

          $scope.testNow = ->
            testNow()
        ]
      });


    ###
      # Show the test account modal
    ###
    testOutgoingModal: ->
      me = @
      inst = @$modal.open({
        templateUrl: 'AdminInterface/TicketAccounts/test-outgoing-modal.html',
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
            ).error(=>
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

  AdminStart_Ctrl_Email.EXPORT_CTRL()