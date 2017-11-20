define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], (Admin_Ctrl_Base, Util) ->
  class Admin_Apps_Ctrl_EditInstanceV2 extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Apps_Ctrl_EditInstanceV2'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$http', 'dpTemplateManager', '$q']

    init: ->
      @instanceId = parseInt(@$stateParams.instanceId)
      @$scope.getController = => return this
      @$scope.setPresaveCallback = (callback) => @presaveCallback = callback
      @$scope.enableCustomFooter = => @$scope.has_own_footer = true
      @presaveCallback = null
      return

    initialLoad: ->
      d = @$q.defer()

      @Api2.sendGet('/apps/' + @instanceId + '?include=app&inline_sideloads=true').then( (result) =>
        @app = result.data.data
        @$scope.appId = @app.id

        try
          @showChangeSettings = 0 < @app.app.manifest.settings.length
        catch e
          @showChangeSettings = false;


        @pack = @app.app
        @packageName = @pack.name
        @pack.icon_48 = @pack.icon_url + '?s=48'

        d.resolve()
      )

      return d.promise

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
        @Api2.sendDelete('/apps/' + @app.id).success( =>

          # If we are viewing with the parent list, we need to remove this
          # app from the list
          if @$scope.$parent?.ListCtrl?
            @$scope.$parent?.ListCtrl.removeAppInstance(@app.id, true)

          # close this view
          window.location.hash = '/apps/apps'
          @$state.go('apps.apps')
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

  Admin_Apps_Ctrl_EditInstanceV2.EXPORT_CTRL()
