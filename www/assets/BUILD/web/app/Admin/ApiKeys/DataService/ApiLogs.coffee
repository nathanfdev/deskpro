define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit
)  ->
  class Admin_ApiKeys_DataService_ApiLogs extends BaseListEdit
    @$inject = ['Api2', '$q']

    init: ->
      @setSubLists(['logs'])
      @filtration = {
        mode: ''
        method: ''
      }

    getFiltration: ->
      @filtration

    _doLoadList: (params) ->
      deferred = @$q.defer()

      @Api2.sendGet('/api_logs?page=' + params.page)
      .success((data) =>
        models = @mutateData(data)
        deferred.resolve models
      )
      .error( (data, status, headers, config) => deferred.reject() )

      return deferred.promise

    _doRefreshList: () ->
      deferred = @$q.defer()
      pagination = @getPagination()
      @Api2.sendGet('/api_logs', {
        page: pagination.logs.page
      }).success((data) =>
          models = @mutateData(data)
          deferred.resolve(models)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise

    mutateData: (data) =>
      models = {logs: data.data, pagination: {logs: {}}}
      models.pagination.logs = {
        total: data.meta.pagination.total
        num_pages: data.meta.pagination.total_pages
        page: data.meta.pagination.current_page
      }

      return models
