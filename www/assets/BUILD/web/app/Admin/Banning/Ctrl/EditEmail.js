define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_Banning_Ctrl_EditEmail extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Banning_Ctrl_EditEmail'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['$stateParams']

    init: ->
      @banData = @DataService.get('Bans')
      @banData.setType('email')
      @email_ban = null

    ###
  #
  ###

    initialLoad: ->
      promise = @banData.loadEditBanData(@$stateParams.ban || null).then( (data) =>

        @email_ban = data.email_ban
        @form = data.form
      )
      return promise

    ###
    #
  ###

    saveForm: ->

      if not @$scope.form_props.$valid
        return

      is_new = !@$stateParams.ban

      promise = @banData.saveFormModel(@email_ban, @form)

      @startSpinner('saving')
      promise.then( =>
        @stopSpinner('saving', true).then( =>
          @Growl.success("Saved")
        )

        @skipDirtyState()
        if is_new
          @$state.go('crm.banning.gocreate_email')
        else
          @$state.go('crm.banning.edit_email', {ban: @email_ban.banned_email})
      )

  Admin_Banning_Ctrl_EditEmail.EXPORT_CTRL()