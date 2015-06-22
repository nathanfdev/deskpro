define [
  'Admin/Main/DataService/Base',
  'Admin/Main/Model/Base',
  'Admin/Main/Collection/OrderedDictionary'
], (
  Admin_Main_DataService_Base,
  Admin_Main_Model_Base,
  Admin_Main_Collection_OrderedDictionary
)  ->
  class Admin_FeedbackCategories_DataService_FeedbackCategories extends Admin_Main_DataService_Base
    constructor: (em, Api, $q) ->
      super(em)
      @$q   = $q
      @Api  = Api

      @loadListPromise = null
      @recs = new Admin_Main_Collection_OrderedDictionary()

    ###*
    * Loads list of records
  *
  * @param reload - (optional) whether to reload list of records or no
  *
  * @return {Promise}
    ###

    loadList: (model, reload) ->

      if @loadListPromise
        return @loadListPromise

      deferred = @$q.defer()

      if not reload and @recs.count()

        deferred.resolve(@recs)
        return deferred.promise

      @Api.sendGet('/feedback_categories').success( (data, status, headers, config) =>

        @_setListData(data.feedback_categories)
        deferred.resolve(@recs)

      , (data, status, headers, config) ->
        deferred.reject()
      )

      @loadListPromise = deferred.promise

      return @loadListPromise

    ###*
        * Removes entity from entity manager
      *
      * @param id
    ###

    remove: (id) ->

      model = @em.getById('feedback_category', id)

      if model?
        @recs.remove(id)
        @em.removeById('feedback_category', 'id')

      @_updateOrderOfData()

    ###
    # Updates entity with new model data provided
    # with new model provided. Or adds it to the list if it doesnt exist.
    ###
    updateModel: (model) ->

      # case of 'no parent'

      if not model.options
        model.options = {parent_id: 0}

      if not model.options.parent_id or model.options.parent_id == "0"
        model.options.parent_id = 0

      # this is due to the reason that in list it's stored as parent_id while in form it's stored in options.parent_id
      model.parent_id = model.options.parent_id

      new_model = @em.createEntity('feedback_category', 'id', model)
      @recs.set(new_model.id, new_model)

      @_updateOrderOfData()

    ###
    # Returns list of feedback_categories where feedback of specified feedback_category could be moved to
    # @param model - specified feedback_category model
    # @return array
    ###
    getListOfMovables: (model) ->

      move_list = []
      parent_id = model.parent_id

      @recs.forEach (key, val) =>
        if !@hasChildren(val) && val.id != model.id
          move_list.push val

      return move_list

    ###
    # Returns list of parent records
    # @param model - specified model for which we want to know possible parent records
    # @return array
    ###

    getListOfParents: (model) ->

      parent_list = [{
        id: '',
        title: 'No Parent'
      }]

      @recs.forEach( (key, val) =>

        if val.id != model.id and not val.parent_id
          parent_list.push(val)
      )

      return parent_list

    ###
    # Returns wherther spcified model has children or not
    # @param model - specified model for which we want to know if it has children or not
    # @return array
    ###

    hasChildren: (model) ->

      for rec in @recs.values()

        if ~~rec.parent_id == model.id
          return true

      return false

    ###*
        * Creates entities for feedback categories raw data
        *
        * @return {Promise}
    ###
    _setListData: (raw_recs) ->

      for rec in raw_recs

        model = @em.createEntity('feedback_category', 'id', rec)
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