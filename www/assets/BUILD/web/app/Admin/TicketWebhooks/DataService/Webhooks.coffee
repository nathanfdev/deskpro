define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit
)  ->
  class Admin_TicketWebhooks_DataService_Webhooks extends BaseListEdit
    @$inject = ['Api', '$q', 'Api2']

    init: ->
      @type = 'webhook'

    url: ->
      return '/webhooks/tickets'

    _doLoadList: ->
      deferred = @$q.defer()

      @Api2.sendGet('/webhooks/tickets?include=ticket_trigger&inline_sideloads=1').then(
        (response) =>
          webhooks = response.data.data
          deferred.resolve(webhooks)
      ).catch((res) =>
        deferred.reject()
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

    _doRemove: (model) ->
      deferred = @$q.defer()

      id = model[@idProp] || 0
      @Api2.sendDelete(@url() + "/#{id}").success(
        () => deferred.resolve()
        ,
        (data, status, headers, config) -> deferred.reject(
          {info: data.error_message, status: status}
        )

      )
      deferred.promise

    _doSave: (model) ->
      deferred = @$q.defer()

      method = 'sendPostJson' # is new
      method = 'sendPutJson' if model[@idProp]? and model[@idProp]

      id = model[@idProp] || 0
      @Api2[method](@url() + "/#{id}", model).then(
        (response) =>
          console.log('after save ', response)
          deferred.resolve(response.data.data)
      ).catch((res) =>
        # data, status, headers, config
        deferred.reject()
      );



      deferred.promise


