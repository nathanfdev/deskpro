define [
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit,
)  ->
  class Admin_TicketFilters_DataService_TicketFilterSets extends BaseListEdit
    @$inject = ['Api2', '$q']
    # Cache of the filter sets.
    @filter_sets = []

    _doLoadList: ->
      deferred = @$q.defer()

      @Api2.sendGet('/ticket_filter_sets')
        .success( (data) =>
          @filter_sets = data.data
          deferred.resolve(data.data)
        )
        .error( (data, status, headers, config) => deferred.reject() )

      return deferred.promise

    _loadListOrCache: ->
      deferred = @$q.defer()

      if @filter_sets? && @filter_sets.length > 0
        deferred.resolve(@filter_sets)
      else
        @_doLoadList().then(
          (data) => deferred.resolve(data)
        ,
          (data) => deferred.reject()
        )

      return deferred.promise

    _cleanUpFilterSet: (filter_set) ->
      clean_filter_set = {
        title: filter_set.title,
        display_order: filter_set.display_order,
        is_default: filter_set.is_default
      }
      
      if filter_set.id?
        clean_filter_set.id = filter_set.id
      
      return clean_filter_set

    ###
    # Save a new filter set.
    #
    # @param {String} The new filter set's name
    # @return {promise}
    ###
    saveTicketFilterSet: (filter_set) ->
      deferred = @$q.defer()
      
      data_promise = null
      if filter_set.id?
        data_promise = @Api2.sendPutJson('/ticket_filter_sets/' + filter_set.id, @_cleanUpFilterSet(filter_set))
      else
        data_promise = @Api2.sendPostJson('/ticket_filter_sets', filter_set)
      
      data_promise
      .success( (data) =>
        deferred.resolve(data)
      )
      .error( (data, status, headers, config) =>
        deferred.reject(data)
      )
      
      return deferred.promise

    blank: ->
      return {
        title: null
      }

    ###
      # Save order of filters
      #
      # @param {Array} orders Array of IDs, in order
      # @return {promise}
    ###
    saveDisplayOrder: (orders) ->
      @Api2.sendPostJson('/ticket_filter_sets/display_order', { display_order: orders })


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
