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

			types = {}
			if id
				types.filter = '/ticket_filters/' + id

			types.agents = '/agents'
			types.teams = '/agent_teams'

			@Api.sendDataGet(types).then( (res) ->
				data = {}
				if res.data.filter
					data.filter = res.data.filter.filter

				data.agents = res.data.agents.agents
				data.teams  = res.data.teams.agent_teams
				deferred.resolve(data)
			, -> deferred.reject())

			return deferred.promise