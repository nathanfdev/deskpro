define ['DeskPRO/Util/Util'], (Util)  ->
  class Admin_Portal_Service_PortalGeneralSettings
    @$inject = ['Api2', '$q']

    constructor: (@Api2, @$q) ->

    init: ->
      # simple counter that other controllers
      # watch to know when the settings object
      # has changed
      @version = 1
      @settingPromise = []
      @settings = null
      @brandId = null

    setBrandId: (brandId) ->
      @brandId = brandId

    ###
    # Unsets loaded settings cache. Next time it is fetched, a HTTP call will be made.
    ###
    reset: ->
      @settings = null

    ###
    # Load settings from the database.
    # If settings were already loaded, those cached values are returned.
    #
    # Note: A COPY of the settings object is returned.
    # You should watch @version to know when you should re-fetch settings.
    #
    # @return {promise}
    ###
    getSettings: ->
      d = @$q.defer()

      if @settingPromise[@brandId]
        @settingPromise[@brandId].then((r) =>
          d.resolve(Util.clone(r, true))
        , (r, s) =>
          d.reject(r, s)
        )
      else if @settings && @settings.brand == @brandId
        d.resolve(Util.clone(@settings, true))
      else
        @_loadSettings().then((r) =>
          d.resolve(Util.clone(r.data.data, true))
        , (r, s) =>
          d.reject(r, s)
        )

      d.promise

    ###
    # Reload settings from the database. Any cached values will be discarded.
    #
    # @return {promise}
    ###
    _loadSettings: ->
      d = @$q.defer()
      @settingPromise[@brandId] = d.promise

      if @brandId
        p = @Api2.sendGet('/settings/brands/'+@brandId+'/portal/general').success((res) =>
          @settings = res.data
          @version += 1
          d.resolve(@settings)
          @settingPromise[@brandId] = null
        ).error((data, status) =>
          d.reject(data, status)
          @settingPromise[@brandId] = null
        )
      else
        p = @$q.when({})

      p

    ###
    # Updates settings with those provided, then will reload
    # settings to make current set up to date.
    ###
    updateSettings: (settings) ->
      d = @$q.defer()

      data = angular.copy(settings);

      # Virtual value we don't want to save it
      delete data.portal_mode;

      @Api2.sendPostJson('/settings/brands/'+@brandId+'/portal/general', data).then((res) =>
        @_loadSettings().then(=>
          d.resolve(res.data.data)
        , =>
          d.resolve(res.data.data)
        )
      , (res, status) =>
        d.reject(res, status)
      )

      d.promise

    updateSettingsTemporary: (settings) ->
      newSettings = Util.merge(@settings, Util.clone(settings, true))

      @version += 1
      @settings = newSettings
