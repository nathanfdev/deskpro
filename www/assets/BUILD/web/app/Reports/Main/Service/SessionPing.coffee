define ->
  class Reports_Main_Service_SessionPing
    constructor: (@Api) ->
      @paused = false
      @interval = null

    pause: -> @paused = true
    resume: -> @paused = false

    startInterval: (timeout = 180000) ->
      if @interval then window.clearInterval(@interval)
      @interval = window.setInterval(=>
        @_autoPing()
      , timeout)

    stopInterval: ->
      if @interval then window.clearInterval(@interval)
      @interval = null

    _autoPing: ->
      return false if @paused
      return @ping()

    ###
      # Ping the session and get a new request token
      #
      # @return {promise}
    ###
    ping: ->
      p = @Api.sendGet('/my/session/renew-request-token')
      p.success( (data) ->
        if data.request_token
          window.DP_REQUEST_TOKEN = data.request_token
          window.DP_SESSION_ID = data.session_id
      )
      return p