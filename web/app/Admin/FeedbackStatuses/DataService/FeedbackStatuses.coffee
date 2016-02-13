define [
  'Admin/Main/DataService/Base',
  'Admin/Main/Model/Base',
  'Admin/Main/Collection/OrderedDictionary'
], (
  Admin_Main_DataService_Base,
  Admin_Main_Model_Base,
  Admin_Main_Collection_OrderedDictionary
)  ->
  class Admin_FeedbackStatuses_DataService_FeedbackStatuses extends Admin_Main_DataService_Base
    constructor: (em, Api, $q) ->
      super(em)
      @$q   = $q
      @Api  = Api

      @loadListPromise = null
      @recs = {
        active_statuses: new Admin_Main_Collection_OrderedDictionary(),
        closed_statuses: new Admin_Main_Collection_OrderedDictionary()
      }

    ###*
    * Loads all feedback statuses
      * Returns a promise.
      *
      * @return {Promise}
    ###
    loadList: (reload) ->

      if @loadListPromise
        return @loadListPromise

      deferred = @$q.defer()

      if not reload and @recs.active_statuses.count() and @recs.closed_statuses.count()

        deferred.resolve(@recs)
        return deferred.promise

      http_def = @Api.sendGet('/feedback_statuses').success( (data, status, headers, config) =>

        @_setListData(data.statuses)
        deferred.resolve(@recs)
      , (data, status, headers, config) ->
        deferred.reject()
      )

      @loadListPromise = deferred.promise

      return @loadListPromise

    ###*
        * Removed entity from entity manager
      *
      * @param id
    ###

    remove: (id) ->

      model = @em.getById('feedback_status', id)

      if model? and model.status_type?
        @recs[model.status_type + '_statuses'].remove(id)
        @em.removeById('feedback_status', 'id')

      @_updateOrderOfData()

    ###
    # Updates entity with new model data provided
  # with new model provided. Or adds it to the list if it doesnt exist.
  ###
    updateModel: (model) ->

      new_model = @em.createEntity('feedback_status', 'id', model)

      if model.status_type? and model.status_type == 'active'
        @recs.active_statuses.set(new_model.id, new_model)

      if model.status_type? and model.status_type == 'closed'
        @recs.closed_statuses.set(new_model.id, new_model)

      @_updateOrderOfData()

      return new_model

    ###
    # Returns list of feedback_statuses where feedback of specified feedback_status could be moved to
  # @param model - specified feedback_status model
    # @return array
    ###

    getListOfMovables: (model) ->

      move_list = []

      @recs[model.status_type + '_statuses'].forEach( (key, val) =>

        if val.id != model.id
          move_list.push(val)
      )

      return move_list

    ###*
        * Creates entities for feedback statuses raw data
        * The thing is that it creates entities for both active and closed statuses
        *
        * @return {Promise}
    ###
    _setListData: (raw_recs) ->

      for rec in raw_recs.active_statuses

        model = @em.createEntity('feedback_status', 'id', rec)
        model.retain()
        @recs.active_statuses.set(model.id, model)

      for rec in raw_recs.closed_statuses

        model = @em.createEntity('feedback_status', 'id', rec)
        model.retain()
        @recs.closed_statuses.set(model.id, model)

    _updateOrderOfData: ->

      @recs.active_statuses.reorder((a, b) ->
        order1 = a.display_order || 0
        order2 = b.display_order || 0

        if order1 == order2
          return 0

        return (order1 < order2) ? -1: 1
      )

      @recs.active_statuses.notifyListeners('changed')

      @recs.closed_statuses.reorder((a, b) ->
        order1 = a.display_order || 0
        order2 = b.display_order || 0

        if order1 == order2
          return 0

        return (order1 < order2) ? -1: 1
      )

      @recs.closed_statuses.notifyListeners('changed')