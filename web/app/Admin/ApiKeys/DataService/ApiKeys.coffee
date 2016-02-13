define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit
)  ->
  class Admin_ApiKeys_DataService_ApiKeys extends BaseListEdit
    @$inject = ['Api', '$q']

    url: ->'/api_keys'

    replayLogEntry: (entry) ->
      deferred = @$q.defer()

      @Api.sendGet("/api_keys/replay/#{entry.id}")
      .success((data, status, headers, config) =>
        @loadList true
        deferred.resolve(data, status)
      )
      .error((data, status, headers, config) -> deferred.reject(data, status))

      deferred.promise

    getLogs: (entry) ->
      deferred = @$q.defer()

      @Api.sendGet("/api_keys/#{entry.id}/logs").success((data, status, headers, config) =>
        deferred.resolve(data, status)
      ).error((data, status, headers, config) -> deferred.reject(data, status))

      deferred.promise

    ###
    # Generate new API key code
    #
    # @param {Object} model api_key model
    # @return {promise}
    ###
    regenerateApiKey: (model) ->
      @Api.sendPostJson('/api_keys/regenerate/' + model.id).success (data) =>
        model.code = data.code
        model.keyString = data.keyString