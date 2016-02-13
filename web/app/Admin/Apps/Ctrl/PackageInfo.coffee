define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Apps_Ctrl_PackageInfo extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Apps_Ctrl_PackageInfo'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$http']

    init: ->
      @packageName = @$stateParams.name;
      return

    initialLoad: ->
      promise = @Api.sendDataGet({
        pack: '/apps/packages/' + @packageName,
      }).then( (result) =>
        @pack = result.data.pack['package']
        if @pack.is_usersource_app
          for the_app in @pack.apps
            if the_app.user_usersource
              @pack.user_usersource_app = the_app
            if the_app.agent_usersource
              @pack.agent_usersource_app = the_app

      )

      return promise

    startDelete: ->
      doDelete = =>
        @Api.sendDelete('/apps/packages/' + @pack.name).success( =>
          @$state.go('apps.go_apps')
        )

      @$modal.open({
        templateUrl: @getTemplatePath('Apps/package-delete-modal.html'),
        controller: ['pack', '$scope', '$modalInstance', (pack, $scope, $modalInstance) ->
          $scope.pack = pack
          $scope.dismiss = ->
            $modalInstance.close();

          $scope.confirm = ->
            $scope.is_loading = true
            doDelete().then(->
              $modalInstance.close();
            )
        ],
        resolve: {
          pack: =>
            return @pack
        }
      });

  Admin_Apps_Ctrl_PackageInfo.EXPORT_CTRL()