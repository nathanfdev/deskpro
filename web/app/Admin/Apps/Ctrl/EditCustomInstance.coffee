define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], (Admin_Ctrl_Base, Util) ->
  class Admin_Apps_Ctrl_EditCustomInstance extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Apps_Ctrl_EditCustomInstance'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = []

    init: ->
      @$scope.setting_values = {}
      @instanceId = parseInt(@$stateParams.custom_id.replace(/^custom_/, ''))

      @$scope.aceLoaded = (editor) ->
        maxH = 500
        updateH = ->
          newHeight = editor.getSession().getScreenLength() * editor.renderer.lineHeight + editor.renderer.scrollBar.getWidth()
          if newHeight > maxH
            newHeight = maxH
          if newHeight < 100
            newHeight = 100

          $(editor.container).height(newHeight)
          editor.resize()

        updateH()
        editor.getSession().on('change', updateH);
        editor.setShowPrintMargin(false)

        $(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor')

      return

    initialLoad: ->
      d = @$q.defer()

      @Api.sendDataGet({
        app: '/apps/instances/' + @instanceId
      }).then( (result) =>
        @app = result.data.app.app;

        @$scope.$parent.ListCtrl.ensureCustomAppInList(@app)

        @Api.sendDataGet({
          pack: '/apps/packages/' + @app.package_name,
          assets: '/apps/custom/' + @instanceId + '/assets'
        }).then( (result) =>
          @pack = result.data.pack['package']

          assets = result.data.assets.assets

          asset_groups = {
            "main":    [],
            "ticket": [],
            "user":    [],
            "org":     []
          };

          app_js = assets.filter((x) -> x.tag == 'app_js')[0]
          if app_js
            asset_groups.main.push({
              title: "App Definition",
              js: app_js.file_content,
              js_id: app_js.id,
              js_name: app_js.name
            })

          asset_groups.ticket = @_getGroupedAssets(assets.filter((x) -> x.name.indexOf('Ticket/') != -1))
          asset_groups.user   = @_getGroupedAssets(assets.filter((x) -> x.name.indexOf('User/') != -1))
          asset_groups.org    = @_getGroupedAssets(assets.filter((x) -> x.name.indexOf('Org/') != -1))

          @asset_groups = asset_groups

          d.resolve()
        )
      )

      d.promise.then(=>
        @$scope.pack = @pack
        @$scope.setting_values = @app.settings

        if not @$scope.setting_values or Util.isArray(@$scope.setting_values)
          @$scope.setting_values = {}

        @$scope.setting_values.dp_app = {title: @app.title}
      )

      d.promise

    _getGroupedAssets: (assets) ->
      groups = []

      app_context = assets.filter((x) -> x.tag == 'js' && x.name.indexOf('Context.js') != -1)[0]
      if app_context
        groups.push({
          title: "JS Controller",
          js: app_context.file_content,
          js_id: app_context.id,
          js_name: app_context.name
        })

      for asset in assets
        if not (asset.tag == 'js' and asset.metadata.group_name) then continue
        html_asset = assets.filter((x) -> x.tag == 'html' and x.metadata?.group_name == asset.metadata.group_name)[0]

        groups.push({
          title: asset.metadata.group_name.replace(/_/g, ' ').replace(/([A-Z])/g, ' $1'),
          js: asset.file_content,
          js_id: asset.id,
          js_name: asset.name
          html: if html_asset then html_asset.file_content else null,
          html_id: if html_asset then html_asset.id else null
          html_name: if html_asset then html_asset.name else null
        })

      return groups

    saveSettings: ->
      postData = {
        settings: @$scope.setting_values
        save_assets: []
      }

      for own _, group of @asset_groups
        for asset in group
          if asset.js_id
            postData.save_assets.push({ id: asset.js_id, content: asset.js })
          if asset.html_id
            postData.save_assets.push({ id: asset.html_id, content: asset.html })

      @startSpinner('saving_settings')
      @Api.sendPostJson("/apps/instances/#{@instanceId}", postData).then(=>
        @stopSpinner('saving_settings').then(=>
          @$scope.$parent.ListCtrl.updateAppTitle(@instanceId, @$scope.setting_values.dp_app.title)
          @Growl.success(@getRegisteredMessage('saved_settings'))
        )
      , ->
        @stopSpinner('saving_settings')
      )

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

  Admin_Apps_Ctrl_EditCustomInstance.EXPORT_CTRL()