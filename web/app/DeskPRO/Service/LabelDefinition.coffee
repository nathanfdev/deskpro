define ['angular'], (angular) ->
  class DeskPRO_Service_LabelDefinition
    loadDefinitions = null
    updateColorForLabel = null

    constructor: (@$q, definitionsPromise) ->
      loadPromise = null
      @definitions =
        tickets: {}
        people: {}
        organizations: {}
        news: {}
        kb: {}
        feedback: {}
        downloads: {}
        chat: {}

      loadDefinitions = =>
        return loadPromise if loadPromise
        d = @$q.defer()

        definitionsPromise.then (data) =>

          for def in data.data
            continue if !def.label? || !def.label_type?
            label = def.label.toLowerCase()
            @definitions[def.label_type] = @definitions[def.label_type] || {}
            @definitions[def.label_type][label] = angular.copy def

          d.resolve @definitions

        loadPromise = d.promise

    all: (label_type) =>
      d = @$q.defer()
      loadDefinitions().then => d.resolve @definitions[label_type]
      d.promise

    get: (label_type, label) ->
      d = @$q.defer()
      label = (label || '').toLowerCase()
      loadDefinitions().then(=>
        if @definitions[label_type]
          val = @definitions[label_type][label]
        else
          val = null
        d.resolve(val)
      )
      d.promise

    update: (_old, _new) ->
      return if !_new.label || !_new.label_type
      label = _new.label.toLowerCase()

      if _old
        delete @definitions[_old.label_type][_old.label.toLowerCase()]

      @definitions[_new.label_type] = @definitions[_new.label_type] || {}
      @definitions[_new.label_type][label] = _new

    remove: (def) ->
      return if !def.label || !def.label_type
      if @definitions[def.label_type]?[def.label.toLowerCase()]?
        delete @definitions[def.label_type][def.label.toLowerCase()]