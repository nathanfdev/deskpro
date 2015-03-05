define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_FeedbackStatuses_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_FeedbackStatuses_Ctrl_List'
    @CTRL_AS = 'FeedbackStatusesList'
    @DEPS    = ['$rootScope', '$scope', 'FeedbackStatusesData', 'em', 'Api', '$state', 'Growl']

    init: ->

      @$scope.activeType = 'active'
      @$scope.closedType = 'closed'

      @feedback_active_statuses = [];
      @feedback_closed_statuses = [];

      @sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest('ul')

          postData = {display_orders: []}

          x = 0
          em = @em

          status_type = 'active'

          $list.find('li').each(->

            x += 10
            feedback_status_id = parseInt($(this).data('id'))

            if feedback_status_id
              feedback_status = em.getById('feedback_status', feedback_status_id)

              if feedback_status
                feedback_status.display_order = x
                status_type = feedback_status.status_type

            postData.display_orders.push(feedback_status_id)
          )

          promise = @Api.sendPostJson('/feedback_statuses/display_order', postData)
          @pingElement('display_orders_' + status_type)
      }

    initialLoad: ->

      list_promise = @FeedbackStatusesData.loadList().then( (recs) =>

        @feedback_active_statuses = recs.active_statuses.values()
        @feedback_closed_statuses = recs.closed_statuses.values()

        @addManagedListener(@FeedbackStatusesData.recs.active_statuses, 'changed', =>

          @feedback_active_statuses = @FeedbackStatusesData.recs.active_statuses.values()
          @ngApply()
        )

        @addManagedListener(@FeedbackStatusesData.recs.closed_statuses, 'changed', =>

          @feedback_closed_statuses = @FeedbackStatusesData.recs.closed_statuses.values()
          @ngApply()
        )
      )

      return @$q.all([list_promise])

    ###
  # Show the delete dlg
  ###

    startDelete: (feedback_status) ->

      move_feedback_statuses_list = @FeedbackStatusesData.getListOfMovables(feedback_status)

      if not move_feedback_statuses_list.length
        @showAlert('@no_delete_last');
        return

      inst = @$modal.open({
        templateUrl: @getTemplatePath('FeedbackStatuses/delete-modal.html'),
        controller: ['$scope', '$modalInstance', 'move_feedback_statuses_list', ($scope, $modalInstance, move_feedback_statuses_list) ->

          $scope.move_feedback_statuses_list = move_feedback_statuses_list
          $scope.selected = {
            move_to_id: move_feedback_statuses_list[0].id
          }

          $scope.confirm = ->
            $modalInstance.close($scope.selected.move_to_id);

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ],
        resolve: {
          move_feedback_statuses_list: =>
            return move_feedback_statuses_list
        }
      });

      inst.result.then( (move_to) =>
        @deleteFeedbackStatus(feedback_status, move_to)
      )

    ###
    # Actually do the delete
  # @param feedback_status - feedback status we want to delete
  # @param move_to - to what status feedback should be moved
    ###

    deleteFeedbackStatus: (feedback_status, move_to) ->

      @Api.sendDelete('/feedback_statuses/' + feedback_status.id, {
        move_to: move_to
      }).success( =>

        @FeedbackStatusesData.remove(feedback_status.id)
        @ngApply()

        # if currently viewing the deleted feedback status, then should need to switch state
        if @$state.current.name == 'portal.feedback_statuses.edit' and parseInt(@$state.params.id) == feedback_status.id
          @$state.go('portal.feedback_statuses')
      )

  Admin_FeedbackStatuses_Ctrl_List.EXPORT_CTRL()