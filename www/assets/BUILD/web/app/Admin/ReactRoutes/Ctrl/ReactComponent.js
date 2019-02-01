define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ReactRoutes_Ctrl_ReactComponent extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_ReactRoutes_Ctrl_ReactComponent';
    }

    init() {
      let routePath = window.location.hash.replace(/#\//, '');
      if (routePath[0] !== '/') {
        routePath = `/${routePath}`;
      }

      const reactProps = {
        routePath
      };

      const element = document.getElementById('react_admin_bundle');

      window.AdminBundle.render(reactProps, element);

      return this.$scope.$on('$destroy', () => window.AdminBundle.unmount(element));
    }
  }
  Admin_ReactRoutes_Ctrl_ReactComponent.initClass();

  return Admin_ReactRoutes_Ctrl_ReactComponent.EXPORT_CTRL();
});
