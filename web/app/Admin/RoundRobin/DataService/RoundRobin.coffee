define [
	'Admin/Main/DataService/BaseListEdit',
], (
	Admin_Main_DataService_BaseListEdit,
)  ->
	class Admin_RoundRobin_DataService_RoundRobin extends Admin_Main_DataService_BaseListEdit
		@$inject = ['Api', '$q']
		_url = '/round_robin'



		_doLoadList: ->
			deferred = @$q.defer()

			@Api.sendGet(_url).success (data) =>
				deferred.resolve data
			.error (data, status, headers, config) ->
				deferred.reject(data)

			deferred.promise



		_doSave: (model) ->
			deferred = @$q.defer()
			method = 'sendPostJson' # is new
			method = 'sendPutJson' if model[@idProp]? and model[@idProp]

			id = model[@idProp] || 0
			@Api[method](_url + "/#{id}", model).success (data) =>
				deferred.resolve data
			.error (data, status, headers, config) =>
				deferred.reject
					info: data.error_message
					status: status

			deferred.promise



		_doRemove: (model) ->
			deferred = @$q.defer()

			id = model[@idProp] || 0
			@Api.sendDelete(_url + "/#{id}").success =>
				deferred.resolve()
			.error (data, status, headers, config) =>
					deferred.reject
						info: data.error_message
						status: status

			deferred.promise
