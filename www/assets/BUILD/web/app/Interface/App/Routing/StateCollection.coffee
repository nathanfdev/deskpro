define ->
  class StateCollection
    constructor: (@factory) ->
      @routes = []
      @whens = []

    add: (id) ->
      r = @factory(id)
      @routes.push r
      return r

    when: (path, to_path) ->
      @whens.push [path, to_path]
      return null