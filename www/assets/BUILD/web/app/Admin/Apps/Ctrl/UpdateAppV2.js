/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Apps_Ctrl_UpdateAppV2 extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Apps_Ctrl_UpdateAppV2';
    }

    init() {
      this.$scope.getController = () => { return this; };

      const reactProps = {
        routePath: `app-install/update/${this.$stateParams.instanceId}`,
        legacyNavigate: this.$state.go.bind(this.$state)
      };

      return window.AdminBundle.render(reactProps, document.getElementById('react_admin_bundle'));
    }
  }
  Admin_Apps_Ctrl_UpdateAppV2.initClass();

  return Admin_Apps_Ctrl_UpdateAppV2.EXPORT_CTRL();
});
