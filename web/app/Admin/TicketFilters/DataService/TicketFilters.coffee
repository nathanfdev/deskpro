define [
	'Admin/Main/DataService/BaseListEdit'
], (
	BaseListEdit,
)  ->
	class Admin_TicketFilters_DataService_TicketFilters extends BaseListEdit
		@$inject = ['Api', '$q']

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/ticket_filters').success( (data) =>
				models = data.filters
				deferred.resolve(models)
			, (data, status, headers, config) ->
				deferred.reject()
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

			promise = @Api.sendPostJson('/ticket_filters/display_order', { display_order: orders })
			return promise


		###
    	# Remove a filter
    	#
    	# @param {Integer} id Filter id
    	# @return {promise}
		###
		deleteFilterId: (id) ->
			promise = @Api.sendDelete('/ticket_filters/' + id).then(=>
				@removeListModelById(id)
			)
			return promise


		###
    	# Get all data needed for the edit filter page
    	#
    	# @param {Integer} id Filter id
    	# @return {promise}
		###
		loadEditFilterData: (id) ->

			deferred = @$q.defer()

			@Api.sendGet('/ticket_filters/' + id).then( (result) ->
				deferred.resolve({
					filter: result.data.filter
				})
			, ->
				deferred.reject()
			)

			return deferred.promise