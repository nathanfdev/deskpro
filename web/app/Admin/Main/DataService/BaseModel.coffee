define [
	'DeskPRO/Util/Angular',
	'DeskPRO/Util/Arrays',
	'DeskPRO/Util/Util',
	'angular',
], (
	Util_Angular,
	Arrays,
	Util,
	angular,
) ->
	###
	# This is a simple base data service that implements some default functionality for
	# loading object model
	###
	class Admin_Main_DataService_BaseModel

		@$inject = ['Api', '$q']

		constructor: ->
			Util_Angular.setInjectedProperties(this, arguments)
			@model             = {}
			@init()


		###
		# An empty hook method for sub-classes
		###
		init: ->
			return



		# model res endpoint
		url: ->
			throw "This method must be implemented by a sub-class"



		# map model from response
		resolveResponse: (response) ->
			response



		# get model promise
		get: (reload) ->
			deferred = @$q.defer()

			if @loaded && !reload?
				deferred.resolve @model
				return deferred.promise

			@_doGet().then(
				(data) =>
					@loaded = true
					deferred.resolve(angular.copy data, @model)
				(res) =>
					deferred.reject res
			)

			deferred.promise



		# load model api call
		_doGet: ->
			deferred = @$q.defer()
			@Api.sendGet(@url()).success (data) =>
				deferred.resolve @resolveResponse(data)
			.error (data, status, headers, config) ->
				deferred.reject(data)

			deferred.promise



		# update model
		set: ->
			@get(true) if !@loaded

			deferred = @$q.defer()
			@_doSet().then(
				(data) =>
					deferred.resolve(angular.copy data, @model)
				(res) =>
					deferred.reject res
			)

			deferred.promise



		# update model api call
		_doSet: ->
			deferred = @$q.defer()

			@Api.sendPutJson(@url(), @model).success (data) =>
				deferred.resolve @resolveResponse(data)
			.error (data, status, headers, config) =>
				deferred.reject
					info: data.error_message
					status: status

			deferred.promise

