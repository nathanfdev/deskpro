define [
  'Admin/Main/Ctrl/Base',
  'Admin/Main/Collection/OrderedDictionary',
], (
  Admin_Ctrl_Base,
  OrderedDictionary
) ->
  class Admin_TicketWebhooks_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketWebhooks_Ctrl_List'
    @CTRL_AS = 'List'
    @DEPS = ['$state', '$stateParams', '$q', 'TicketAccountsData', '$timeout']

    init: ->
      @webhooks        = []
      @dpWebhooks = @DataService.get('Webhooks')
      @dpTriggers = @DataService.get('TriggersNew')

    ###
    # Loads the triggers list
    ###
    initialLoad: ->
      @dpWebhooks.loadList().then( (list) => @webhooks = list)
      return

    onWebhookAdded: (webhook) ->
      @dpWebhooks.mergeDataModel(webhook)
      @dpWebhooks.loadList().then(
        (list) =>
          @webhooks = [].concat(list)
      )

    ###
      # Sorts triggers into display groups
      ###
    sortTriggers: ->
      return null

    addTrigger: (webhook) ->
      @$state.go('tickets.webhooks.trigger-create', { webhookId: webhook.id })
      return

    onTriggerAdded: (trigger, webhookId) ->
      @dpWebhooks.loadList(true).then(
        (list) =>
          @webhooks = [].concat(list)
          @$state.go('tickets.webhooks.trigger-edit', {webhookId: webhookId, id: trigger.data.id})
      )
      return

    changeEnabledStatus: (webhook) ->
      webhook.is_enabled = webhook.is_enabled ? false : true

      @dpWebhooks.set(webhook).then(
        (wh) =>
          @dpWebhooks.loadList().then(
            (list) =>
              @webhooks = [].concat(list)
          )
      )
      return

    ###
    # Update the enabled state of a trigger
    ###
    changeEnabledStatusTrigger: (trigger) ->
      return @dpTriggers.saveEnabledStateById(trigger.id, trigger.is_enabled)

    ###
    # Show the delete dlg
    ###
    startTriggerDelete: (trigger_id) ->

      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketTriggers/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then( =>
        @dpWebhooks.deleteTriggerById(trigger_id).then(=>
          @dpWebhooks.loadList().then(
            (list) =>
              @webhooks = [].concat(list)
              @$state.go('tickets.webhooks')
          )
        )
      )

    startDelete: (webhookId) ->
      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketWebhooks/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then( =>
        model = @dpWebhooks.removeListModelById(webhookId)
        @dpWebhooks.remove(model).then(=>
          @dpWebhooks.loadList().then(
            (list) =>
              @webhooks = [].concat(list)
              @$state.go('tickets.webhooks')
          ).catch((err) =>
            console.log('err ', err)
          )
        )
      )



  Admin_TicketWebhooks_Ctrl_List.EXPORT_CTRL()
