define ->
  class StateCollection
    constructor: (@factory) ->
      @routes = []

    add: (id) ->
      r = @factory(id)
      @routes.push r
      return r