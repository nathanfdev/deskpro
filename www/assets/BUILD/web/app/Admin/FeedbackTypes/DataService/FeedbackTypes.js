define [
  'Admin/Main/DataService/Base',
  'Admin/Main/Model/Base',
  'Admin/Main/Collection/OrderedDictionary'
], (
  Admin_Main_DataService_Base,
  Admin_Main_Model_Base,
  Admin_Main_Collection_OrderedDictionary
)  ->
  class Admin_FeedbackTypes_DataService_FeedbackTypes extends Admin_Main_DataService_Base
    constructor: (em, Api, $q) ->
      super(em)
      @$q   = $q
      @Api  = Api

      @loadListPromise = null
      @recs = new Admin_Main_Collection_OrderedDictionary()

    ###*
    * Loads all feedback types
      * Returns a promise.
      *
      * @return {Promise}
    ###
    loadList: (reload) ->

      if @loadListPromise
        return @loadListPromise

      deferred = @$q.defer()

      if not reload and @recs.count() and @recs.count()

        deferred.resolve(@recs)
        return deferred.promise

      http_def = @Api.sendGet('/feedback_types').success( (data, status, headers, config) =>

        @_setListData(data.types)
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

      model = @em.getById('feedback_type', id)

      if model?
        @recs.remove(id)
        @em.removeById('feedback_type', 'id')

      @_updateOrderOfData()

    ###
    # Updates entity with new model data provided
  # with new model provided. Or adds it to the list if it doesnt exist.
  ###
    updateModel: (model) ->

      new_model = @em.createEntity('feedback_type', 'id', model)
      @recs.set(new_model.id, new_model)

      @_updateOrderOfData()

      return new_model

    ###
    # Returns list of feedback_types where feedback of specified feedback_type could be moved to
  # @param model - specified feedback_type model
    # @return array
    ###

    getListOfMovables: (model) ->

      move_list = []

      @recs.forEach( (key, val) =>

        if val.id != model.id
          move_list.push(val)
      )

      return move_list

    ###*
        * Creates entities for feedback types raw data
        *
        * @return {Promise}
    ###
    _setListData: (raw_recs) ->

      for rec in raw_recs

        model = @em.createEntity('feedback_type', 'id', rec)
        model.retain()
        @recs.set(model.id, model)

    ###
    # Reorders the data of this data service
  # Useful for cases of drag&drop ordering of data
    ###

    _updateOrderOfData: ->

      @recs.reorder((a, b) ->
        order1 = a.display_order || 0
        order2 = b.display_order || 0

        if order1 == order2
          return 0

        return (order1 < order2) ? -1: 1
      )

      @recs.notifyListeners('changed')