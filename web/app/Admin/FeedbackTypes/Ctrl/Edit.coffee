define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_FeedbackTypes_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_FeedbackTypes_Ctrl_Edit'
    @CTRL_AS = 'FeedbackTypesEdit'
    @DEPS    = ['Api', 'Growl', 'FeedbackTypesData', '$stateParams', '$modal']

    init: ->

      @feedback_type = {}
      @usergroups = []
      @selected_usergroups = {}

      return

    initialLoad: ->

      if not @$stateParams.id

        return

      else

        data_promise = @Api.sendDataGet({
          feedback_type: '/feedback_types/' + @$stateParams.id,
          usergroups: '/user_groups'
        }).then((result) =>

          @feedback_type = result.data.feedback_type.feedback_type
          @usergroups = result.data.usergroups.groups

          ids = _.pluck(@feedback_type.usergroups, 'id')

          for id in ids
            @selected_usergroups[id] = true
        )

        return @$q.all([data_promise])

    ###
      # Saves the current form
      #
      # @return {promise}
    ###
    saveFeedbackType: ->

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