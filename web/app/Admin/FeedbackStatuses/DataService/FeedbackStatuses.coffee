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

			return new_model
