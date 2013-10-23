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

			@loadStatusesListPromise = null
			@statuses = new Admin_Main_Collection_OrderedDictionary()

		###*
		* Loads all feedback statuses
    	* Returns a promise.
    	*
    	* @return {Promise}
		###
		loadStatusesList: (reload) ->

			if @loadStatusesListPromise
				return @loadStatusesListPromise

			deferred = @$q.defer()
			if not reload and @statuses.count()
				deferred.resolve(@statuses)
				return deferred.promise

			http_def = @Api.sendGet('/feedback_statuses').success( (data, status, headers, config) =>
				@statuses = data.statuses

				deferred.resolve(@statuses)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			@loadStatusesListPromise = deferred.promise

			return @loadStatusesListPromise
