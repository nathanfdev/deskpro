define ['AdminStart/Ctrl/StartBase'], (StartBase) ->
  class AdminStart_Ctrl_Cron extends StartBase
    @CTRL_ID = 'AdminStart_Ctrl_Cron'
    @DEPS    = ['$location', '$timeout']

    init: ->
      return

    startWaiting: ->
      @$scope.is_waiting = true
      @start_time = (new Date()).getTime() / 1000
      @$scope.boot_errors = null
      @$scope.error_type = null
      @pollWaiting()

    pollWaiting: ->
      @getCronStatus().success( (data) =>
        if data.last_run
          @$location.path('/email')
        else
          if data.cron_boot_errors
            @$scope.is_waiting = false
            @$scope.error_type = 'boot'
            @$scope.boot_errors = data.cron_boot_errors
          else
            now = (new Date()).getTime() / 1000
            if now - @start_time > 66
              @$scope.is_waiting = false
              @$scope.error_type = 'timeout'
            else
              @$timeout( =>
                @pollWaiting()
              , 2000)
      ).error( =>
        @$timeout( =>
          @pollWaiting()
        , 2000) if @$scope.is_waiting
      )

    getCronStatus: ->
      return @Api.sendGet('/server/cron-status')

  AdminStart_Ctrl_Cron.EXPORT_CTRL()