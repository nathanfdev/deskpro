define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_Nav extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Portal_Ctrl_Nav'
    @CTRL_AS   = 'Ctrl'

    init: ->
      @settings = {}
      @portalSettings = @DataService.get 'PortalGeneralSettings'

      @$scope.$watch('Ctrl.portalSettings.version', =>
        @portalSettings.getSettings().then((s) => @settings = s)
      )

  Admin_Portal_Ctrl_Nav.EXPORT_CTRL()
