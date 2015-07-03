define ->
  class AppState
    constructor: (@Api) ->
      @didCronRun = false;
      @checkT = =>
        @Api.sendGet('/server/cron-status').success((data) =>
          if data.last_run
            @didCronRun = true
          else
            setTimeout(=>
              @checkT()
            , 750)
        )
      @checkT()
      return

    hasCronRun: ->
      return @didCronRun