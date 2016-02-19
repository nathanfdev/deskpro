define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit
)  ->
  class Admin_ApiKeys_DataService_ApiTags extends BaseListEdit
    @$inject = ['Api2', '$q']

    getTags: (id) ->
      deferred = @$q.defer()
      @Api2.sendGet('/api_tags/'+id)
      .success((data) =>
        deferred.resolve data.data
      )
      .error( (data, status, headers, config) => deferred.reject() )

      deferred.promise
    updateTags: (title, value, id) ->
      @Api2.sendPutJson('/api_tags/'+id, {action: title, value: value})
