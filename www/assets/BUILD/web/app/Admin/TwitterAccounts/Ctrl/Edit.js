define [
  'Admin/Main/Ctrl/Base',
  'underscore'
], (
  Admin_Ctrl_Base,
  _
) ->
  class Admin_TwitterAccounts_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_TwitterAccounts_Ctrl_Edit'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['$stateParams']

    init: ->
      @twitterAccountData = @DataService.get('TwitterAccounts')
      @twitter_account = null

    initialLoad: ->
      promise = @twitterAccountData.loadEditTwitterAccountData(@$stateParams.id || null).then( (data) =>

        @twitter_account  = data.twitter_account
        @form = data.form
      )
      return promise

    saveForm: ->

      @twitter_account.persons = []

      for own key, value of @selected_agents
        if value
          agent = _.findWhere(@agents, {id: parseInt(key)})
          @twitter_account.persons.push(agent.id) if agent

      if not @$scope.form_props.$valid
        return

      is_new = !@twitter_account.id

      promise = @twitterAccountData.saveFormModel(@twitter_account, @form)

      @startSpinner('saving')
      promise.then( =>
        @stopSpinner('saving', true).then(=>
          @Growl.success("Saved")
        )

        @skipDirtyState()
        if is_new
          @$state.go('twitter.accounts.gocreate')
      )

  Admin_TwitterAccounts_Ctrl_Edit.EXPORT_CTRL()