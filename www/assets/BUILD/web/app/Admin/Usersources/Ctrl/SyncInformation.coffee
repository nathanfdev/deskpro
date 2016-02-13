define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'Admin/Usersources/Helper/UsersourceTypeDecider', 'moment']
, (Admin_Ctrl_Base, Util, Admin_Usersources_Helper_UsersourceTypeDecider, moment) ->
  class Admin_Usersources_Ctrl_SyncInformation extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Usersources_Ctrl_SyncInformation'
    @CTRL_AS = 'Ctrl'
    @DEPS = ['$http', 'dpTemplateManager', '$interval']


    init: ->
      @instanceId = @$stateParams.id
      @permission_groups = [];
      @$scope.getController = => return this
      @$scope.setPresaveCallback = (callback) => @presaveCallback = callback
      @$scope.enableCustomFooter = false
      @usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(@$state);
      @presaveCallback = null
      @app = null
      @$scope.$on '$destroy', => @interval && @$interval.cancel(@interval)


    initialLoad: ->
      promise = @refresh()

      @interval = @$interval(() =>
        @refresh()
      , 10000)

      return promise

    refresh: ->
      d = @$q.defer()
      d2 = @$q.defer()

      @Api.sendDataGet({
        app: '/apps/instances/' + @instanceId
      }).then((result) =>
        @app = result.data.app?.app;

        @$scope.app = @app
        @$scope.appId = @app?.id

        @Api.sendDataGet({
          extra_info: '/usersources/' + @usersourceType + '/app-' + @instanceId + '/extra-details',
          sync_info: '/usersources/sync/info/' + @instanceId,
          pack: '/apps/packages/' + @app.package_name
        }).then((result) =>
          @pack = result.data.pack['package']
          @$scope.usersource_details = result.data.extra_info?.usersource_details
          @packageName = @pack.name
          @$scope.pack = @pack
          @$scope.setting_values = @app.settings
          if not @$scope.setting_values || Util.isArray(@$scope.setting_values)
            @$scope.setting_values = {}
          @$scope.setting_values.dp_app = {title: @app.title}

          sync_log = result.data.sync_info.sync_log

          if sync_log
            if not sync_log.phase_1_running
              sync_log.phase_1_time_readable = moment(sync_log.date_start).from(sync_log.date_end, true)
            else
              sync_log.phase_1_time_readable = '-'

            if sync_log.phase_2_show
              if not sync_log.phase_2_running
                sync_log.phase_2_time_readable = moment(sync_log.date_phase_2_start).from(sync_log.date_phase_2_end, true)
              else
                sync_log.phase_2_time_readable = '-'

          @$scope.sync_log = sync_log
          @$scope.ListCtrl = @listCtrl()

          d.resolve()
        )
      )
      return d.promise

    listCtrl: ->
      return @$scope.$parent?.ListCtrl || {running_now: false, refresh: =>}

  Admin_Usersources_Ctrl_SyncInformation.EXPORT_CTRL()