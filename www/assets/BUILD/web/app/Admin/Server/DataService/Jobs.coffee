define [
  'Admin/Main/DataService/BaseListEdit',
  'moment'
], (
  BaseListEdit,
  moment
)  ->
  class Admin_Server_DataService_Jobs extends BaseListEdit
    @$inject = ['Api2', '$q']

    init: ->
      @pagination = {
        page: 1
      }

    _doLoadList: () ->
      deferred = @$q.defer()

      @Api2.sendGet('/jobs?page=' + @pagination.page)
      .success((data) =>
        models = @mutateData(data)
        deferred.resolve models
      )
      .error( => deferred.reject() )

      return deferred.promise

    _doRefreshList: () ->
      deferred = @$q.defer()
      pagination = @getPagination()
      @Api2.sendGet('/jobs', {
        page: pagination.page
      }).success((data) =>
          models = @mutateData(data)
          deferred.resolve(models)
      , ->
        deferred.reject()
      )

      return deferred.promise

    mutateData: (data) ->
      models = []
      for model in data.data
        models.push model
      models.pagination = {
        total: data.meta.pagination.total
        num_pages: data.meta.pagination.total_pages-1
        page: data.meta.pagination.current_page
      }

      return models

    loadJob: (id) ->
      deferred = @$q.defer()

      @Api2.sendGet('/jobs/' + id).success((data) =>
        deferred.resolve(data.data)
      , ->
        deferred.reject()
      )

      deferred.promise
