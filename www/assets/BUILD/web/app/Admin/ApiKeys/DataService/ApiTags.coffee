define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit
)  ->
  class Admin_ApiKeys_DataService_ApiTags extends BaseListEdit
    @$inject = ['Api2', '$q']

    getTags: (id) ->
      deferred = @$q.defer()
      @Api2.sendGet('/api_tags/'+id+'/flatten')
      .success((data) =>
        deferred.resolve data.data
      )
      .error( (data, status, headers, config) => deferred.reject() )

      deferred.promise
    updateTags: (tags, id) ->
      deferred = @$q.defer()
      @Api2.sendPutJson('/api_tags/'+id, {"tags": tags})
      .success((data) =>
        deferred.resolve data.data
      )
      .error( (data, status, headers, config) => deferred.reject() )
