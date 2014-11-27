define ->
  class TemplateManager
    constructor: (@TemplateLoader, @$templateCache, @$q) ->
      @pending = []
      @pendingNames = {}
      @sendPending = {}

    commonName: (view) -> return view

    ###
    # Mark a view to be loaded next time we are loading templates
    #
    # @param {String} view
    ###
    queue: (view) ->
      view = @commonName(view)
      if not @$templateCache.get(view) and not @pendingNames[view] and not @sendPending[view]
        @pending.push(view)
        @pendingNames[view] = true


    ###
    # Sets a template in the template cache
    #
    # @param {String} view
    # @param {String} source
    ###
    setTemplate: (view, source) ->
      view = @commonName(view)
      @$templateCache.put(view, source)


    ###
    # Removes a template from the cache
    #
    # @param {String} view
    ###
    removeTemplate: (view) ->
      @$templateCache.remove(@commonName(view))


    ###
    # Execute the pending loads by ending the http request.
    #
    # @return {promise}
    ###
    loadPending: ->
      if not @pending.length
        d = @$q.defer()
        d.resolve()
        return d.promise

      preloadTpls = @TemplateLoader.load(@pending)

      @pending = []
      @sendPending = @pendingNames
      @pendingNames = {}

      for own k, v of @sendPending
        @sendPending[k] = preloadTpls

      preloadTpls.then( (data) =>
        for tpl in data
          @$templateCache.put(tpl.id, tpl.source)

        for own k, v of @sendPending
          if v == preloadTpls
            @sendPending[k] = null
            delete @sendPending[k]
      )

      return preloadTpls


    ###
    # Gets the template source if it is already loaded, or null if it isnt
    #
    # @return {String|null}
    ###
    getNow: (view) ->
      view = @commonName(view)
      return @$templateCache.get(view) || null


    ###
    # Loads a template source along with any others that are queued.
    #
    # @return {promise}
    ###
    queue: (view) ->
      view = @commonName(view)
      exist = @$templateCache.get(view)
      if exist || exist == ""
        d = @$q.defer()
        d.resolve(@$templateCache.get(view))
        return d.promise

      if @sendPending[view]
        d = @$q.defer()
        @sendPending[view].then(=>
          d.resolve(@$templateCache.get(view))
        )
        return d.promise

      @load(view)
      promise = @loadPending()
      defer = @$q.defer()

      promise.then(=>
        tpl = @$templateCache.get(view)
        if tpl || tpl == ""
          defer.resolve(tpl)
        else
          console.log("Failed to load %s", view)
          defer.reject("failed")
      )

      return defer.promise