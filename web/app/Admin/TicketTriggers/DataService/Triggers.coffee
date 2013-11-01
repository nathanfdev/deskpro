define [
	'Admin/Main/DataService/Base',
	'Admin/Main/Model/Base',
	'Admin/Main/Collection/OrderedDictionary'
], (
	Admin_Main_DataService_Base,
	Admin_Main_Model_Base,
	Admin_Main_Collection_OrderedDictionary
)  ->
	class Admin_TicketAccounts_DataService_TicketAccounts extends Admin_Main_DataService_Base
		constructor: (type, em, Api, $q) ->
			super(em)
			@type = type
			@$q   = $q
			@Api  = Api

			@loadListPromise = null
			@recs = new Admin_Main_Collection_OrderedDictionary()

		###
		# Loads list of accounts
    	#
    	# @return {Promise}
		###
		loadList: (reload) ->

			if @loadListPromise
				return @loadListPromise

			deferred = @$q.defer()
			if not reload and @recs.count()
				deferred.resolve(@recs)
				return deferred.promise

			http_def = @Api.sendGet("/ticket_triggers/#{@type}").success( (data) =>
				@_setListData(data.triggers)
				deferred.resolve(@recs)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			@loadListPromise = deferred.promise

			return @loadListPromise

		_setListData: (triggers) ->
			@recs.clear()
			@recs.addArray(triggers)

		loadTrigger: (triggerId) ->
			deferred = @$q.defer()
			@Api.sendGet("/ticket_triggers/#{triggerId}").success( (data) ->
				deferred.resolve(data.trigger)
			)
			return deferred.promise

		###
    	# Removes a record
    	###
		removeTriggerModel: (id) ->
			@recs.remove(id)

		###
    	# Updates the first-class model (title, etc)
    	# with account provided. Or adds it to the list if it doesnt exist.
    	###
		updateTriggerModel: (model) ->
			exist = @recs.get(model.id)
			if exist
				for own k, v of model
					exist[k] = v
			else
				@recs.set(model, model)

			return model

		###*
		* Adds a new model to the existing list (eg was just created)
    	*
    	* @return {Admin_Main_Model_Base}
		###
		addTriggerModel: (model) ->
			@recs.set(model.id, model)
			return model