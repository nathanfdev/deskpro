define [
	'Admin/Main/DataService/BaseListEdit',
], (
	Admin_Main_DataService_BaseListEdit,
)  ->
	class Admin_Agents_DataService_Agents extends Admin_Main_DataService_BaseListEdit
		@$inject = ['Api', '$q']

		url: -> '/agents'

		resolveResponse: (response) ->
			models = []
			for data in response.agents
				data.agent.permissions = data.perms
				models.push data.agent

			models

		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet(@url(), {full: 1}).success( (data) =>
				deferred.resolve @resolveResponse(data)
			, (data, status, headers, config) ->
				deferred.reject()
			)

			deferred.promise
