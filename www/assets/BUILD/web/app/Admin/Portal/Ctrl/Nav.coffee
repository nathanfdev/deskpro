define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_Nav extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Portal_Ctrl_Nav'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = ['$timeout']

    init: ->
      @settings = {
        apps_kb: true,
        apps_news: true,
        apps_downloads: true,
        apps_feedback: true,
        iface_portal: true
        portal_mode: 'publish'
      }
      @portalSettings = @DataService.get 'PortalGeneralSettings'

      @$scope.$watch('Ctrl.portalSettings.version', =>
        @portalSettings.getSettings().then((s) => @settings = s)
      )

      depth = @$state.current.name.split('.').length
      if depth == 1
        @$timeout(->
          $('.dp-layout-appnav').find('li').first().find('a').first().click();
        , 10)
        return

  Admin_Portal_Ctrl_Nav.EXPORT_CTRL()
