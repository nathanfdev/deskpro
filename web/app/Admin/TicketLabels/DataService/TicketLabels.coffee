define [
	'Admin/Main/DataService/Base',
	'Admin/Main/Model/Base',
	'Admin/Main/Collection/OrderedDictionary'
], (Admin_Main_DataService_Base, Admin_Main_Model_Base, Admin_Main_Collection_OrderedDictionary)  ->
	class Admin_TicketLabels_DataService_TicketLabels
		constructor: (Api, $q) ->
			@$q = $q
			@Api = Api

			@loadListPromise = null
			@recs = []

		###*
		* Loads list of labels
    	*
    	* @return {Promise}
		###
		loadList:    (reload) ->
			if @loadListPromise
				return @loadListPromise

			deferred = @$q.defer()
			if not reload and @recs.length
				deferred.resolve(@recs)
				return deferred.promise

			http_def = @Api.sendGet('/ticket_labels').success((data, status, headers, config) =>
				@recs = data.labels
				deferred.resolve(@recs)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			@loadListPromise = deferred.promise

			return @loadListPromise

		getObjectByLabel: (label) ->
			return _.findWhere(@recs, {'label': label})

		remove: (label_object) ->
			i = @recs.indexOf(label_object)
			if i > -1
				@recs.splice(i, 1)

		updateModel: (label_object) ->
			i = @recs.indexOf(label_object)
			if i > -1
				@recs[i] = label_object
			else
				@recs.push(label_object)

			return label_object

		addToList: (label_object) ->
			return @updateModel(label_object)