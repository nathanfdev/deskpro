define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketStatuses_Ctrl_EditHiddenDeleted extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketStatuses_Ctrl_EditHiddenDeleted'
    @CTRL_AS = 'TicketStatusEdit'
    @DEPS = []

    init: ->
      @$scope.getCount = => @$scope.$parent.TicketStatusesList?.getStatusCount('hidden_deleted')
      @$scope.settings = {
        auto_purge_time: 604800
      }
      @$scope.times = [
        {id: 86400, label: "1 day"},
        {id: 259200, label: "3 days"},
        {id: 432000, label: "5 days"},
        {id: 604800, label: "1 week"},
        {id: 1209600, label: "2 weeks"},
        {id: 1814400, label: "3 weeks"},
        {id: 2592000, label: "1 month"},
        {id: 5184000, label: "2 months"},
        {id: 7776000, label: "3 months"},
        {id: 15552000, label: "6 months"},
        {id: 23328000, label: "9 months"},
        {id: 31536000, label: "1 year"},
        {id: 63072000, label: "2 years"}
      ]
      return

    initialLoad: ->
      promise = @Api.sendGet("/ticket_statuses/deleted").success( (data) =>
        @$scope.settings.auto_purge_time = data.deleted_info.auto_purge_time
      )

      return promise

    saveSettings: ->
      @startSpinner('saving_settings')
      promise = @Api.sendPostJson('/ticket_statuses/deleted/settings', @$scope.settings).then( =>
        @stopSpinner('saving_settings')
      )

      return promise

    startPurge: ->
      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketStatuses/modal-purge-deleted.html'),
        controller: ['$scope', '$modalInstance', 'Api', ($scope, $modalInstance, Api) =>
          $scope.dismiss = =>
            $modalInstance.dismiss();

          $scope.confirm = =>
            purgeNow()

          purgeNow = ->
            $scope.is_loading = true
            Api.sendDelete('/ticket_statuses/deleted/purge').success( (data) ->
              $scope.is_done = true
              $scope.count = data.count
            ).then ( ->
              $scope.is_loading = false
            )
        ]
      });

  Admin_TicketStatuses_Ctrl_EditHiddenDeleted.EXPORT_CTRL()