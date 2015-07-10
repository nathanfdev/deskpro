define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit,
)  ->
  class Admin_TicketFilters_DataService_TicketFilterViews extends BaseListEdit
    @$inject = ['Api2', '$q']
    # Cache of the filter sets.
    @filter_views = []

    _doLoadList: ->
      deferred = @$q.defer()

      @Api2.sendGet('/ticket_filter_views')
        .success( (data) =>
          @filter_views = data.data
          deferred.resolve(data.data)
        )
        .error( (data, status, headers, config) => deferred.reject() )

      return deferred.promise

    _loadListOrCache: ->
      deferred = @$q.defer()

      if @filter_views? && @filter_views.length > 0
        deferred.resolve(@filter_views)
      else
        @_doLoadList().then(
          (data) => deferred.resolve(data)
        ,
          (data) => deferred.reject()
        )

      return deferred.promise

    ###
      # Get all data needed for the edit filter page
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    loadEditFilterViewData: (id) ->
      deferred = @$q.defer()

      @_doLoadList().then(
        (data) =>
          filter_view = (filter for filter in data when filter.id is id)
          if filter_view?
            deferred.resolve(filter_view[0])
          else
            deferred.reject()
      ,
        (data) => deferred.reject()
      )

      return deferred.promise
