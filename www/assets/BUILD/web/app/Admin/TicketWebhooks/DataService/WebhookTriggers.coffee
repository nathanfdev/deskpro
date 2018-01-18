define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit,
)  ->
  class Admin_TicketWebhooks_DataService_WebhookTriggers extends BaseListEdit
    @$inject = ['Api', '$q', 'Api2']

    init: ->
      @type = 'webhook'

    _doLoadList: ->
      deferred = @$q.defer()
      @Api2
        .sendGet('/webhooks/tickets')
        .success(
          (response) =>
            webhooks = response.data
            deferred.resolve(webhooks)
         ,
          (data, status, headers, config) -> deferred.reject()
        );

      return deferred.promise

    deleteTriggerById: (triggerId) ->
      webhook = null
      trigger = null
      for model in @listModels
        for trigger in model.triggers
          if trigger[@idProp] == triggerId
            webhook = model
          break if webhook
        break if webhook

      deferred = @$q.defer()
      if !webhook
        return Promise.reject(new Error("could not find parent webhook for trigger id: #{triggerId}"))

      deferred = @$q.defer()
      @Api2.sendDelete("/webhooks/#{webhook[@idProp]}/triggers/#{triggerId}").success(
        () =>
          webhook.triggers = webhook.triggers.filter(
            (t) => return t[@idProp] != triggerId
          )
          deferred.resolve(trigger)
        ,
        (data, status, headers, config) -> deferred.reject()
      )

      return deferred.promise



