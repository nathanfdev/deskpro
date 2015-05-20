define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'Admin/Usersources/Helper/UsersourceTypeDecider']
, (Admin_Ctrl_Base, Util, Admin_Usersources_Helper_UsersourceTypeDecider) ->
  class Admin_Usersources_Ctrl_SyncInformation extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Usersources_Ctrl_SyncInformation'
    @CTRL_AS = 'Ctrl'
    @DEPS = ['$http', 'dpTemplateManager']


    init: ->
      @instanceId = @$stateParams.id
      @permission_groups = [];
      @$scope.getController = => return this
      @$scope.setPresaveCallback = (callback) => @presaveCallback = callback
      @$scope.enableCustomFooter = false
      @usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(@$state);
      @presaveCallback = null
      @app = null



    initialLoad: ->
      d = @$q.defer()
      d2 = @$q.defer()

      @listCtrl().refresh().then =>
        enabled = 0
        @listCtrl().usersources.map (source) =>
          s = source.usersource
          return if 'agent' != s.type
          return if 'Application\\DeskPRO\\Usersource\\Adapter\\DeskPRO' == s.source_type
          enabled++ if s.is_enabled
        @$scope.can_disable_deskpro = enabled > 0

      @Api.sendDataGet({
        app: '/apps/instances/' + @instanceId
      }).then((result) =>
        @app = result.data.app?.app;

        @$scope.app = @app
        @$scope.appId = @app?.id

        if @app
          @Api.sendDataGet({
            extra_info: '/usersources/' + @usersourceType + '/app-' + @instanceId + '/extra-details',
            pack: '/apps/packages/' + @app.package_name
          }).then((result) =>
            @pack = result.data.pack['package']
            @$scope.usersource_details = result.data.extra_info?.usersource_details
            @packageName = @pack.name
            d.resolve()
          )
        else
          @usersourceId = @instanceId
          @Api.sendGet('/usersources/' + @usersourceType + '/' + @usersourceId).then((result) =>
            @usersource = result.data.usersource
            d.resolve()
          )
      )

      d.promise.then(=>
# we do nothing here if its a direct usersource, but if its an app we have some work t do
        if not @app
          d2.resolve()
        else
# this is an app instance

          if @permission_groups.length == 0
            @Api.sendGet('/agent_groups').then((res) =>
              res.data.groups.forEach((val) =>
                @permission_groups.push({"value": val.id.toString(), "label": val.title})
              )
            )

          @$scope.pack = @pack
          @$scope.setting_values = @app.settings
          if not @$scope.setting_values || Util.isArray(@$scope.setting_values)
            @$scope.setting_values = {}
          @$scope.setting_values.dp_app = {title: @app.title}

          @$scope.has_display_settings = @pack.settings_def.filter((x) -> x.type != 'hidden').length > 0
          form_template = @packageName + '/AdminInterface/Install/settings.html'
          installCtrl = null
          loadingAssets = []

          getResourcePath = (tag, name) =>
            asset = @pack.assets.filter((x) -> x.tag == tag && x.name == name)[0]
            if asset
              cachebust = window.DP_BUILD_TIME
              asset.blob.relative_url + '?' + cachebust
            else
              null

          if path = getResourcePath('html', 'AdminInterface/Install/settings.html')
            loadingAssets.push(@$http.get(path, {responseType: "text"}).success((data) =>
              @dpTemplateManager.setTemplate(form_template, data)
            ))
          if path = getResourcePath('js', 'AdminInterface/Install/settings.js')
            jsDeferred = @$q.defer()
            require([path], (c) =>
              installCtrl = c
              jsDeferred.resolve()
            )
            loadingAssets.push(jsDeferred.promise)

          if loadingAssets.length
            @$q.all(loadingAssets).then(=>
              if installCtrl
                @$scope.install_ctrl = installCtrl
              else
                @$scope.install_ctrl = [=>
                  return
                ]

              if form_template
                @$scope.form_template = form_template
                @$scope.default_form = false
              else
                @$scope.default_form = true

              d2.resolve()
            )
          else
            @$scope.default_form = true
            d2.resolve()
      )

      return d2.promise

    listCtrl: ->
      @$scope.$parent?.ListCtrl || {refresh: =>}

  Admin_Usersources_Ctrl_SyncInformation.EXPORT_CTRL()