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
      console.log "Load list"
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
    # Save a new filter set.
    #
    # @param {String} The new filter set's name
    # @return {promise}
    ###
    saveTicketFilterSet: (name) ->
      deferred = @$q.defer()
      
      @Api2.sendPostJson(
        '/ticket_filter_views',
        { "title": name }
      )
      .success( (data) =>
        deferred.resolve(data)
      )
      .error( (data, status, headers, config) =>
        deferred.reject(data)
      )
      
      return deferred.promise

    ###
      # Save order of filters
      #
      # @param {Array} orders Array of IDs, in order
      # @return {promise}
    ###
    saveDisplayOrder: (orders) ->
      @Api2.sendPostJson('/ticket_filter_views/display_order', { display_order: orders })


    ###
      # Remove a filter
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    deleteFilterId: (id) ->
      promise = @Api2.sendDelete('/ticket_filters/' + id).then(=>
        @removeListModelById(id)
      )
      return promise

    ###
      # Get all data needed for the edit filter page
      #
      # @param {Integer} id Filter id
      # @return {promise}
    ###
    loadEditFilterSetData: (id) ->
      deferred = @$q.defer()

      @_doLoadList().then(
        (data) =>
          filter_set = (filter for filter in data when filter.id is id)
          if filter_set?
            deferred.resolve(filter_set[0])
          else
            deferred.reject()
      ,
        (data) => deferred.reject()
      )

      return deferred.promise
