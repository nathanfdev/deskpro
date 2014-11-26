define ['require', 'Admin/Main/Ctrl/Base', 'Admin/Usersources/Helper/UsersourceTypeDecider'
], (require, Admin_Ctrl_Base, Admin_Usersources_Helper_UsersourceTypeDecider) ->
  class Admin_Apps_Ctrl_PackageInstall extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Apps_Ctrl_PackageInstall'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$state', '$http', 'dpTemplateManager']

    init: ->
      @packageName = @$stateParams.name.replace(/\.install$/, '');
      @usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(@$state);
      @$scope.getController = => return this
      @$scope.setPresaveCallback = (callback) => @presaveCallback = callback
      @$scope.enableCustomFooter = => @$scope.has_own_footer = true
      @presaveCallback = null
      @permission_groups = []
      return

    initialLoad: ->
      deferred = @$q.defer()

      @Api.sendDataGet({
        pack: '/apps/packages/' + @packageName,
        agent_groups: '/agent_groups'
      }).then( (result) =>
        @pack = result.data.pack['package']
        @$scope.pack = @pack

        for val in result.data.agent_groups.groups
          @permission_groups.push({"value": val.id.toString(), "label": val.title})

        form_template = @packageName + '/Install/install.html'
        installCtrl = null
        loadingAssets = []
        @$scope.has_display_settings = @pack.settings_def.filter( (x) -> x.type != 'hidden').length > 0

        @$scope.setting_values = { dp_app: { title: @pack.title }}

        for setting in @pack.settings_def
          if setting.default_value
            @$scope.setting_values[setting.name] = setting.default_value

        getResourcePath = (tag, name) =>
          asset = @pack.assets.filter((x) -> x.tag == tag && x.name == name)[0]
          return if asset then asset.blob.relative_url else null

        if path = getResourcePath('html', 'AdminInterface/Install/install.html')
          loadingAssets.push(@$http.get(path, { responseType: "text"}).success((data) =>
            @dpTemplateManager.setTemplate(form_template, data)
          ))
        if path = getResourcePath('js', 'AdminInterface/Install/install.js')
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

            deferred.resolve()
          )
        else
          @$scope.default_form = true
          deferred.resolve()

      )

      return deferred.promise

    installApp: ->
      @startSpinner('saving_settings')
      if @presaveCallback
        @presaveCallback(@$scope.setting_values).then( =>
          @doInstall().catch(=>
            @stopSpinner('saving_settings', true)
          )
        , =>
          @stopSpinner('saving_settings', true)
        )
      else
        @doInstall().catch(=>
          @stopSpinner('saving_settings', true)
        )

    cancelInstall: ->
      if @usersourceType == 'user'
        @$state.go('crm.usersources')
      else if @usersourceType == 'agent'
        @$state.go('agents.usersources')
      else
        @$state.go('apps.apps.package', {name: @pack.name});

    doInstall: ->
      listCtrl = null
      if @$scope.$parent.ListCtrl?.addAppInstance?
        listCtrl = @$scope.$parent.ListCtrl

      setting_values = @$scope.setting_values
      pack = @pack
      usersourceType = @usersourceType

      defer = @$q.defer()
      modalInstance = @$modal.open({
        templateUrl: @getTemplatePath('Apps/install-progress-modal.html'),
        controller: 'Admin_Apps_Ctrl_InstallProgress',
        resolve: {
          pack:           -> pack
          setting_values: -> setting_values
          usersourceType: -> usersourceType
        }
      }).result.then( (info) =>
        defer.resolve(info)
      , (info) =>
        defer.reject(info)
      )

      defer.promise.then( (info) =>
        if listCtrl
          instanceInfo = {
            id: info.id,
            title: setting_values.dp_app.title,
            package_name: @pack.name,
            package: @pack
          }
          listCtrl.addAppInstance(instanceInfo)

        if @usersourceType == 'user'
          @$scope.$parent?.ListCtrl?.refresh()
          @$state.go('crm.usersources.id', {id: info.id})
        else if @usersourceType == 'agent'
          @$scope.$parent?.ListCtrl?.refresh()
          @$state.go('agents.usersources.id', {id: info.id})
        else
          @$state.go('apps.apps.instance', {id: info.id});
      )

      return defer.promise

  Admin_Apps_Ctrl_PackageInstall.EXPORT_CTRL()