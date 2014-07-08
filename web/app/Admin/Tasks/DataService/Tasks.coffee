define ->
	class Tasks

		_url = '/tasks/settings'

		constructor: (@Api, @$q) ->
			@settings = {}


		load: ->
			deferred = @$q.defer()

			@Api.sendGet(_url).success(
				(data) =>
					@settings = data
					deferred.resolve @settings
				(data, status, headers, config) ->
					deferred.reject()
			)

			deferred.promise

		save: ->
			deferred = @$q.defer()

			@Api.sendPutJson(_url, @settings).success( (data) =>
				deferred.resolve()
			, (data, status, headers, config) ->
				deferred.reject()
			)

			deferred.promise
