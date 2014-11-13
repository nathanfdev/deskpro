define [
  'Admin/Main/DataService/BaseModel',
], (
  BaseModel,
)  ->
  class LabelSettings extends BaseModel

    init: ->
      @loaded = {}
      @model = {}

    url: (type) ->
      throw "Type must be defined" if !type
      "/labels/#{type}/settings"



    # get model promise
    get: (type, reload) ->
      throw "Type must be defined" if !type
      deferred = @$q.defer()

      if @loaded[type]? && !reload?
        deferred.resolve @model[type]
        return deferred.promise

      @_doGet(type).then(
        (data) =>
          @model[type] = {} if !@model[type]?
          @loaded[type] = true
          deferred.resolve(angular.copy data, @model[type])
        (res) =>
          deferred.reject res
      )

      deferred.promise



    # load model api call
    _doGet: (type) ->
      deferred = @$q.defer()
      @Api.sendGet(@url(type)).success (data) =>
        deferred.resolve @resolveResponse(data)
      .error (data, status, headers, config) ->
          deferred.reject(data)

      deferred.promise



    # update model
    set: (type) ->
      throw "Type must be defined" if !type
      @get(type) if !@loaded[type]?

      deferred = @$q.defer()
      @_doSet(type).then(
        (data) =>
          deferred.resolve(angular.copy data, @model[type])
        (res) =>
          deferred.reject res
      )

      deferred.promise



    # update model api call
    _doSet: (type) ->
      deferred = @$q.defer()

      @Api.sendPutJson(@url(type), @model[type]).success (data) =>
        deferred.resolve @resolveResponse(data)
      .error (data, status, headers, config) =>
          deferred.reject
            info: data.error_message
            status: status

      deferred.promise


