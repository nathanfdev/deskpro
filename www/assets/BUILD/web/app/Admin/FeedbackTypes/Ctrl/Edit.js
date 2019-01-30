define [
  'Admin/Main/Ctrl/Base',
  'underscore'
], (
  Admin_Ctrl_Base,
  _
) ->
  class Admin_FeedbackTypes_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_FeedbackTypes_Ctrl_Edit'
    @CTRL_AS = 'FeedbackTypesEdit'
    @DEPS    = ['Api', 'Growl', 'FeedbackTypesData', '$stateParams', '$modal']

    init: ->

      @feedback_type = {}
      @usergroups = []
      @selected_usergroups = {}




    initialLoad: ->
      promises = []
      promises.push @Api.sendDataGet({usergroups: '/user_groups'}).then (result) =>
        @usergroups = result.data.usergroups.groups

      if @$stateParams.id
        promises.push @Api.sendDataGet({
          feedback_type: '/feedback_types/' + @$stateParams.id,
        }).then (result) =>
          @feedback_type = result.data.feedback_type.feedback_type
          ids = _.pluck(@feedback_type.usergroups, 'id')
          for id in ids
            @selected_usergroups[id] = true

      @$q.all promises



    ###
      # Saves the current form
      #
      # @return {promise}
    ###
    saveFeedbackType: ->

      @feedback_type.brand = @$stateParams.brandId
      @feedback_type.usergroups = []

      for own key, value of @selected_usergroups
        if value
          usergroup = _.findWhere(@usergroups, {id: parseInt(key)})
          @feedback_type.usergroups.push(usergroup.id) if usergroup

      if not @$scope.form_props.$valid
        return

      @startSpinner('saving_feedback_type')

      if @feedback_type.id
        is_new = false
        promise = @Api.sendPostJson('/feedback_types/' + @feedback_type.id, {feedback_type: @feedback_type})
      else
        is_new = true
        promise = @Api.sendPutJson('/feedback_types', {feedback_type: @feedback_type})

      promise.success((result) =>

        @feedback_type.id = result.id
        @feedback_type.brand = result.brand

        @stopSpinner('saving_feedback_type', true).then(=>
          @Growl.success(@getRegisteredMessage('saved_feedback_type'))
        )

        @FeedbackTypesData.updateModel(@feedback_type)

        @skipDirtyState()

        if is_new
          @$state.go('portal.feedback_types.gocreate')
        else
          @$state.go('portal.feedback_types')
      )
      promise.error((info, code) =>
        @stopSpinner('saving_feedback_type', true)
        @applyErrorResponseToView(info)
      )

      return promise

  Admin_FeedbackTypes_Ctrl_Edit.EXPORT_CTRL()