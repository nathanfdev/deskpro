define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Apps_Ctrl_UpdateAppV2 extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Apps_Ctrl_UpdateAppV2'

    init: ->
      @$scope.getController = => return this

      reactProps = {
        routePath: 'app-install/update/' + @$stateParams.instanceId
        legacyNavigate: @$state.go.bind(@$state)
      }

      window.AdminBundle.render(reactProps, document.getElementById('react_admin_bundle'));

  Admin_Apps_Ctrl_UpdateAppV2.EXPORT_CTRL()
