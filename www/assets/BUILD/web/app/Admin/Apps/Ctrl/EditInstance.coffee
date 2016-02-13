define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], (Admin_Ctrl_Base, Util) ->
  class Admin_Apps_Ctrl_EditInstance extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Apps_Ctrl_EditInstance'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$http', 'dpTemplateManager', '$q']

    init: ->
      @instanceId = parseInt(@$stateParams.id)
      @$scope.getController = => return this
      @$scope.setPresaveCallback = (callback) => @presaveCallback = callback
      @$scope.enableCustomFooter = => @$scope.has_own_footer = true
      @presaveCallback = null
      return

    initialLoad: ->
      d = @$q.defer()
      d2 = @$q.defer()

      service = {
        groups: @DataService.get('AgentGroups'),
        agents: @DataService.get('Agents')
      }

      @Api.sendDataGet({
        app: '/apps/instances/' + @instanceId
      }).then( (result) =>
        @app = result.data.app.app;
        @$scope.appId = @app.id

        getData = {
          pack: '/apps/packages/' + @app.package_name
        }

        if @app.with_permissions
          service.agents.all();
          service.groups.all();

        @Api.sendDataGet(getData).then( (result) =>
          @pack = result.data.pack['package']
          @packageName = @pack.name

          if not @app.with_permissions
            d.resolve()
          else
            @$q.all([service.agents.all(), service.groups.all()]).then( (res) =>
              @$scope.perms = {}
              @$scope.perms.type = @app.perm_type
              @$scope.perms.usergroups = res[1].map((x) =>
                return {
                  id: x.id,
                  name: x.title,
                  checked: @app.permissions.usergroup_ids.indexOf(x.id) != -1
                }
              )
              @$scope.perms.agents = res[0].map((x) =>
                return {
                  id: x.id,
                  name: x.display_name,
                  checked: @app.permissions.person_ids.indexOf(x.id) != -1
                }
              )

              @$scope.selected_agents_count = => @$scope.perms.agents.filter((x) -> x.checked).length
              @$scope.selected_groups_count = => @$scope.perms.usergroups.filter((x) -> x.checked).length

              d.resolve()
            )
        )
      )

      d.promise.then( =>
        @$scope.pack = @pack
        @$scope.setting_values = @app.settings
        if not @$scope.setting_values || Util.isArray(@$scope.setting_values)
          @$scope.setting_values = {}
        @$scope.setting_values.dp_app = {title: @app.title}

        @$scope.has_display_settings = @pack.settings_def.filter( (x) -> x.type != 'hidden').length > 0
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
          loadingAssets.push(@$http.get(path, { responseType: "text"}).success((data) =>
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

    saveSettings: ->
      @startSpinner('saving_settings')
      if @presaveCallback
        @presaveCallback().then( =>
          @doSaveSettings().catch(=>
            @stopSpinner('saving_settings', true)
          )
        , =>
          @stopSpinner('saving_settings', true)
        )
      else
        @doSaveSettings().finally(=>
          @stopSpinner('saving_settings', true)
        )

    doSaveSettings: ->

      if @app.with_permissions and @$scope.perms.type? and @$scope.perms.type == 'set'
        perms = {
          type: 'set',
          usergroup_ids: @$scope.perms.usergroups.filter((x) -> x.checked).map((x) -> x.id)
          person_ids: @$scope.perms.agents.filter((x) -> x.checked).map((x) -> x.id)
        }
      else
        perms = { type: 'global' }

      postData = {
        settings: @$scope.setting_values,
        permissions: perms
      }

      @Api.sendPostJson("/apps/instances/#{@instanceId}", postData).then(=>
        @stopSpinner('saving_settings').then(=>
          @$scope.$parent.ListCtrl.updateAppTitle(@instanceId, @$scope.setting_values.dp_app.title)
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      )

    ###
      # Shows readme modal window
      ###
    showReadme: ->
      @$modal.open({
        templateUrl: @getTemplatePath('Apps/readme-modal.html'),
        controller: ['$scope', '$modalInstance', 'pack', ($scope, $modalInstance, pack) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.pack = pack
        ],
        resolve: {
          pack: =>
            return @pack
        }
      })


    ###
    # SHow delete modal
    ###
    startDelete: ->
      doDelete = =>
        @Api.sendDelete('/apps/instances/' + @app.id).success( =>

          # If we are viewing with the parent list, we need to remove this
          # app from the list
          if @$scope.$parent?.ListCtrl?
            @$scope.$parent?.ListCtrl.removeAppInstance(@app.id)

          # close this view
          @$state.go('apps.apps')
        )

      @$modal.open({
        templateUrl: @getTemplatePath('Apps/instance-delete-modal.html'),
        controller: ['app', '$scope', '$modalInstance', (app, $scope, $modalInstance) ->
          $scope.app = app
          $scope.dismiss = ->
            $modalInstance.close();

          $scope.confirm = ->
            $scope.is_loading = true
            doDelete().then(->
              $modalInstance.close();
            )
        ],
        resolve: {
          app: =>
            return @app
        }
      });

  Admin_Apps_Ctrl_EditInstance.EXPORT_CTRL()