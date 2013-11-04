define [
	'Admin/Main/DataService/BaseListEdit'
], (
	BaseListEdit,
)  ->
	class Admin_TicketFilters_DataService_TicketEscalations extends BaseListEdit
		@$inject = ['Api', '$q']

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet('/ticket_escalations').success( (data) =>
				models = data.escalations
				deferred.resolve(models)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			return deferred.promise


		###
    	# Save order of escalations
    	#
    	# @param {Array} orders Array of IDs, in order
    	# @return {promise}
		###
		saveRunOrder: (orders) ->
			for id, idx in orders
				model = @findListModelById(id)
				if model
					model.display_order = idx

			promise = @Api.sendPostJson('/ticket_escalations/run_order', { display_order: orders })
			return promise


		###
    	# Remove a filter
    	#
    	# @param {Integer} id Filter id
    	# @return {promise}
		###
		deleteEscalationById: (id) ->
			promise = @Api.sendDelete('/ticket_escalations/' + id).then(=>
				@removeListModelById(id)
			)
			return promise


		###
    	# Get all data needed for the edit filter page
    	#
    	# @param {Integer} id Filter id
    	# @return {promise}
		###
		loadEditEscalationData: (id) ->

			deferred = @$q.defer()

			@Api.sendGet('/ticket_escalations/' + id).then( (result) ->
				deferred.resolve({
					filter: result.data.filter
				})
			, ->
				deferred.reject()
			)

			return deferred.promise