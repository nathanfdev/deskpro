define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'Admin/Usersources/Helper/UsersourceTypeDecider']
, (Admin_Ctrl_Base, Util, Admin_Usersources_Helper_UsersourceTypeDecider) ->
  class Admin_Usersources_Ctrl_EditInstance extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Usersources_Ctrl_EditInstance'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$http', 'dpTemplateManager']

    init: ->
      @instanceId = @getInstanceId()
      @$scope.getController = => return this
      @$scope.setPresaveCallback = (callback) => @presaveCallback = callback
      @$scope.enableCustomFooter = => @$scope.has_own_footer = true
      @usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(@$state)
      @presaveCallback = null
      @app = null

    getInstanceId: -> @$stateParams.id

    initialLoad: ->
      d = @$q.defer()
      d2 = @$q.defer()

      brands_promise = @Api2.sendGet('brands').then( (res) =>
        @brands = res.data.data
      )

      usersource_detailsv2 = @Api2.sendGet('user_sources/'+ @usersourceType + '/' + @instanceId).then( (res) =>
        @$scope.usersource_detailsv2 = res.data.data
      )

      @listCtrl().refresh().then =>
        enabled = 0
        @listCtrl().usersources.map (source) =>
          s = source.usersource
          return if @usersourceType != s.type
          return if 'Application\\DeskPRO\\Usersource\\Adapter\\DeskPRO' == s.source_type
          enabled++ if s.is_enabled
        @$scope.can_disable_deskpro = enabled > 0

      @Api.sendDataGet({
        app: '/apps/instances/' + @instanceId
      }).then( (result) =>
        @app = result.data.app?.app

        @$scope.app = @app
        @$scope.appId = @app?.id

        if @app
          @Api.sendDataGet({
            extra_info: '/usersources/' + @usersourceType + '/app-' + @instanceId + '/extra-details',
            pack: '/apps/packages/' + @app.package_name
          }).then( (result) =>
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

      d.promise.then( =>
        # we do nothing here if its a direct usersource, but if its an app we have some work t do
        if not @app
          d2.resolve()
        else
          # this is an app instance

          @$scope.pack = @pack
          @$scope.setting_values = @app.settings
          if not @$scope.setting_values || Util.isArray(@$scope.setting_values)
            @$scope.setting_values = {}
          @$scope.setting_values.dp_app = { title: @app.title }

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

      return @$q.all([d2.promise, brands_promise, usersource_detailsv2])



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
      postData = {
        settings: @$scope.setting_values
      }

      @Api.sendPostJson("/apps/instances/#{@instanceId}", postData).then(=>
        @Api.sendGet('/usersources/' + @usersourceType + '/app-' + @instanceId + '/extra-details').then((result) =>
          @$scope.usersource_details = result.data.usersource_details
        )
        @stopSpinner('saving_settings').then(=>
          @listCtrl().refresh()
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      )

      @Api.sendPostJson('/user_sources/' + @usersourceType + '/' + @usersourceId, @$scope.usersource_detailsv2)



    doSaveUsersource: ->
      postData = {
        title: @usersource.title,
        is_enabled: @usersource.is_enabled
      }

      @Api.sendPostJson('/usersources/' + @usersourceType + '/' + @usersourceId, postData).then(
        =>
          @listCtrl().refresh()
          @Growl.success @getRegisteredMessage 'saved_settings'
        (res) =>
          msg = @getRegisteredMessage(res.data.error_code) || res.data.error_message || ''
          @Growl.error msg
      )

      @Api.sendPostJson('/user_sources/' + @usersourceType + '/' + @usersourceId, @$scope.usersource_detailsv2)

    saveUsersource: ->
      @startSpinner('saving_settings')
      @doSaveUsersource().finally => @stopSpinner('saving_settings')


    cannotDeleteUsersource: ->
      alert "The DeskPRO usersource cannot be uninstalled. However, you can disable it by unchecking the box on the form and saving."



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
          @listCtrl().refresh()

          # close this view
          @$state.go('^')
        )

      @$modal.open({
        templateUrl: @getTemplatePath('Apps/instance-delete-modal.html'),
        controller: ['app', '$scope', '$modalInstance', (app, $scope, $modalInstance) ->
          $scope.app = app
          $scope.dismiss = ->
            $modalInstance.close()

          $scope.confirm = ->
            $scope.is_loading = true
            doDelete().then(->
              $modalInstance.close()
            )
        ],
        resolve: {
          app: =>
            return @app
        }
      })



    listCtrl: ->
      @$scope.$parent?.ListCtrl || {refresh: =>}

    handleBrand: (brandId, e) ->
      index = @$scope.usersource_detailsv2.brands.indexOf brandId
      if index == -1
        @$scope.usersource_detailsv2.brands.unshift brandId
      else
        if (@$scope.usersource_detailsv2.brands.length > 1)
          @$scope.usersource_detailsv2.brands.splice(index, 1)
        else
          alert "Usersource needs to be linked to at least one Brand"
          $(e.target).prop("checked", true)
          return true



  Admin_Usersources_Ctrl_EditInstance.EXPORT_CTRL()
