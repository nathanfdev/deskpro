define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ReactRoutes_Ctrl_ReactComponent extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ReactRoutes_Ctrl_ReactComponent'

    init: ->
      routePath = window.location.hash.replace(/#\//, '');
      if (routePath[0] != '/')
        routePath = '/' + routePath;

      reactProps = {
        routePath: routePath
      }

      element = document.getElementById('react_admin_bundle')

      window.AdminBundle.render(reactProps, element)

      @$scope.$on('$destroy', ->
        window.AdminBundle.unmount(element);
      )

  Admin_ReactRoutes_Ctrl_ReactComponent.EXPORT_CTRL()
