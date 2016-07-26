define ->
  class AdminUpdateWatcher_Ctrl_Main
    @CTRL_AS   = 'Ctrl'
    @CTRL_ID   = 'AdminUpdateWatcher_Ctrl_Main'
    @DEPS      = ['$q', '$timeout', '$scope', '$http', '$interval']

    @EXPORT_CTRL: () ->
      ctrl_def = @DEPS.slice(0)
      ctrl_def.push(@)
      if not window.DP_CTRL_REG
        window.DP_CTRL_REG = []

      window.DP_CTRL_REG.push([@CTRL_ID, ctrl_def])
      return this

    constructor: (args...) ->
      @ctrl_is_loading = true
      if @constructor.DEPS.length != args.length
        console.error("Dependencies are not the same as passed args: %o != %o", @constructor.DEPS, args)
        return

      for arg, i in args
        arg_name = @constructor.DEPS[i]
        if arg_name
          @[arg_name] = arg

      if @constructor.CTRL_AS
        @$scope[@constructor.CTRL_AS] = @


      @has_init = false
      @init()
      @has_init = true

    init: ->
      @$scope.showFinishedNextInfo = false
      @$scope.logUrl = window.DP_BASE_URL+'/__serverinfo/logs/updater?auth=' + window.DP_SERVERINFO_AUTH
      @refreshStatus().then(=>
        @$scope.initDone = true
      )

      @timeId = @$interval(=>
        @refreshStatus()
      , 3500)

    refreshStatus: ->
      @$http.get(window.DP_BASE_URL+'/admin/updater-status/' +  window.DP_SERVERINFO_AUTH + '?status').then((res) =>
        # if the status starts on anything but finished, then
        # the UI should not show the 'next' notice
        if @$scope.info?.status != 'finished'
          @$scope.showFinishedNextInfo = true

        @$scope.info = res.data
      )

  AdminUpdateWatcher_Ctrl_Main.EXPORT_CTRL()
