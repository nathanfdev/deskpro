define [
  'Admin/Main/Ctrl/Base', 'angular'
], (
  Admin_Ctrl_Base, angular
) ->
  class Admin_Banning_Ctrl_EditIp extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Banning_Ctrl_EditIp'
    @CTRL_AS   = 'EditCtrl'
    @DEPS      = ['$stateParams']

    init: ->
      @banData = @DataService.get('Bans')
      @banData.setType('ip')
      @ip_ban = null

    ###
  #
  ###

    initialLoad: ->
      promise = @banData.loadEditBanData(@$stateParams.ban || null).then( (data) =>

        @ip_ban = data.ip_ban
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

      promise = @banData.saveFormModel(@ip_ban, @form)

      @startSpinner('saving')
      promise.then( =>
        @stopSpinner('saving', true).then(=>
          @form = angular.copy(@ip_ban)
          @Growl.success("Saved")
        )

        @skipDirtyState()
        if is_new
          @$state.go('crm.banning.gocreate_ip')
        else
          @$state.go('crm.banning.edit_ip', {ban: @ip_ban.id})
      )

  Admin_Banning_Ctrl_EditIp.EXPORT_CTRL()