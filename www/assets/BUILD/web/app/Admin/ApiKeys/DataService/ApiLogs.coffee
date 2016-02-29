define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit
)  ->
  class Admin_ApiKeys_DataService_ApiLogs extends BaseListEdit
    @$inject = ['Api2', '$q']

    init: ->
      @filtration = {
        mode: ''
        method: ''
      }
      @pagination = {
        page: 1
      }

    getFiltration: ->
      return @filtration

    getOptions: ->
      deferred = @$q.defer()
      @Api2.sendGet('/api_logs_options')
      .success((data) =>
        deferred.resolve data.data
      )
      .error( (data, status, headers, config) => deferred.reject() )

      deferred.promise

    updateOptions: (options)->
      @Api2.sendPutJson('/api_logs_options', options)
      .success((data) =>
        deferred.resolve data.data
      )
      .error( (data, status, headers, config) => deferred.reject() )

    _doLoadList: () ->
      deferred = @$q.defer()

      @Api2.sendGet('/api_logs?page=' + @pagination.page)
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
        page: pagination.page
      }).success((data) =>
          models = @mutateData(data)
          deferred.resolve(models)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      return deferred.promise

    mutateData: (data) ->
      models = []
      models.push model for model in data.data
      models.pagination = {
        total: data.meta.pagination.total
        num_pages: data.meta.pagination.total_pages
        page: data.meta.pagination.current_page
      }

      return models

    loadLog: (id) ->
      deferred = @$q.defer()

      @Api2.sendGet('/api_logs/' + id + '?include=data').success((data) =>
        deferred.resolve(data.data)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      deferred.promise

    replay: (model, mode) ->
      deferred = @$q.defer()
      @Api2.sendPostJson('/api_logs/' + model.id + '/replay', {mode: mode, request_id: model.request_id})
      .success((data) =>
        deferred.resolve(data.data)
      )

      deferred.promise