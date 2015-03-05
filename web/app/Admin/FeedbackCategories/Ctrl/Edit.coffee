define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_FeedbackCategories_Ctrl_Edit extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_FeedbackCategories_Ctrl_Edit'
    @CTRL_AS = 'FeedbackCategoriesEdit'
    @DEPS    = ['Api', 'Growl', 'FeedbackCategoriesData', '$stateParams', '$modal']

    init: ->

      @feedback_category = {}
      @feedback_categories_parent_list = {}

      @addManagedListener(@FeedbackCategoriesData.recs, 'changed', =>
        @feedback_categories_parent_list = @FeedbackCategoriesData.getListOfParents(@feedback_category)
        @ngApply()
      )

      return

    initialLoad: ->
      requests = [
        @FeedbackCategoriesData.loadList(),
        if @$stateParams.id
          @Api.sendDataGet({
            feedback_category: '/feedback_categories/' + @$stateParams.id
          })
      ]

      promise = @$q.all(requests).then((result) =>

        if @$stateParams.id
          @feedback_category = result[1].data.feedback_category.feedback_category
          @feedback_categories_parent_list = @FeedbackCategoriesData.getListOfParents(@feedback_category)
      )

      return promise

    ###
      # Saves the current form
      #
      # @return {promise}
    ###
    saveForm: ->

      if not @$scope.form_props.$valid
        return

      is_new = !@feedback_category.id

      @startSpinner('saving_feedback_category')

      if is_new
        promise = @Api.sendPutJson('/feedback_categories', {feedback_category: @feedback_category})
      else
        promise = @Api.sendPostJson('/feedback_categories/' + @feedback_category.id, {feedback_category: @feedback_category})

      promise.success((result) =>

        @feedback_category.id = result.id

        @stopSpinner('saving_feedback_category', true).then(=>
          @Growl.success(@getRegisteredMessage('saved_feedback_category'))
        )

        @FeedbackCategoriesData.updateModel(@feedback_category)

        @skipDirtyState()

        if is_new
          @$state.go('portal.feedback_categories.gocreate')
        else
          @$state.go('portal.feedback_categories')
      )

      promise.error((info, code) =>
        @stopSpinner('saving_feedback_category', true)
        @applyErrorResponseToView(info)
      )

      return promise


  Admin_FeedbackCategories_Ctrl_Edit.EXPORT_CTRL()