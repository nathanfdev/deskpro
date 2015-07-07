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

    ###
      # Save order of filters
      #
      # @param {Array} orders Array of IDs, in order
      # @return {promise}
    ###
    saveDisplayOrder: (orders) ->
      for id, idx in orders
        model = @findListModelById(id)
        if model
          model.display_order = idx

      promise = @Api2.sendPostJson('/ticket_filters/display_order', { display_order: orders })
      return promise


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

      @_loadListOrCache().then(
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
