define ['DeskPRO/Util/Util'], (Util)  ->
  class Admin_Portal_Service_PortalGeneralSettings
    @$inject = ['Api', '$q']

    constructor: (@Api, @$q) ->

    init: ->
      # simple counter that other controllers
      # watch to know when the settings object
      # has changed
      @version = 1
      @settingPromise = null
      @settings = null

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

      if @settingPromise
        @settingPromise.then((r) =>
          d.resolve(Util.clone(r, true))
        , (r, s) =>
          d.reject(r, s)
        )
      else if @settings
        d.resolve(Util.clone(@settings, true))
      else
        @_loadSettings().then((r) =>
          d.resolve(Util.clone(r, true))
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
      @settingPromise = d.promise

      p = @Api.sendGet('/settings/portal/general').success((data) =>
        @settings = data.portal_settings
        @version += 1
        d.resolve(@settings)
        @settingPromise = null
      ).error((data, status) =>
        d.reject(data, status)
        @settingPromise = null
      )

      p

    ###
    # Updates settings with those provided, then will reload
    # settings to make current set up to date.
    ###
    updateSettings: (settings) ->
      d = @$q.defer()

      postData = {
        portal_settings: settings
      }

      @Api.sendPostJson('/settings/portal/general', postData).success((data) =>
        @_loadSettings().then(=>
          d.resolve(data)
        , =>
          d.resolve(data)
        )
      , (data, status) =>
        d.reject(data, status)
      )

      d.promise

    updateSettingsTemporary: (settings) ->
      newSettings = Util.merge(@settings, Util.clone(settings, true))

      if not Util.equals(newSettings, @settings)
        @version += 1
        @settings = newSettings