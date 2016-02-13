define ['json3'], (JSON) ->
  ###
  # Thin wrapper around localStorage. Does nothing if localStorage is not supported.
  ###
  class DeskPRO_Util_LocalStore
    isSupported: ->
      try
        return window['localStorage']? && window['localStorage'] != null
      catch e
        return false

    ###
    # Get a value from local storage
    #
    # @param {String} k
    # @param mixed    default_val
    # @return {String}
    ###
    get: (k, default_val = null) ->
      return null if not @isSupported()
      return localStorage[k] if localStorage[k]?
      return default_val


    ###
    # Set a value in local storage
    #
    # @param {String} k
    # @param {String} val
    # @return void
    ###
    set: (k, val) ->
      return if not @isSupported()
      localStorage[k] = val
      return


    ###
    # Check if a key exists in local storage
    #
    # @param {String} k
    # @return {Boolean}
    ###
    has: (k) ->
      return @isSupported() && localStorage[k]?


    ###
    # Remove something from localstorage
    #
    # @param {String} k
    # @return void
    ###
    remove: (k) ->
      return if not @isSupported()
      localStorage[k] = null
      delete localStorage[k]
      return


    ###
    # Set an object in local storage
    #
    # @param {String} k
    # @param {Object} val
    # @return void
    ###
    setObject: (k, val) ->
      return if not @isSupported()
      localStorage[k] = JSON.stringify(val)
      return


    ###
    # Get an object from local storage
    #
    # @param {String} k
    # @param mixed    default_val
    # @return void
    ###
    getObject: (k, default_val) ->
      v = @get(k)
      return default_val if v == null

      try
        v = JSON.parse(v)
      catch
        return default_val

      return v

  return new DeskPRO_Util_LocalStore()