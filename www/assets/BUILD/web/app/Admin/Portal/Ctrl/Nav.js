define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Portal_Ctrl_Nav extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Portal_Ctrl_Nav';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$timeout', '$state', '$stateParams'];
    }

    init() {
      const depth = this.$state.current.name.split('.').length;
      if (depth === 1) {
        this.$timeout(() => $('.dp-layout-appnav').find('li').first().find('a').first().click()
        , 10);
        return;
      }
    }
  }
  Admin_Portal_Ctrl_Nav.initClass();


  return Admin_Portal_Ctrl_Nav.EXPORT_CTRL();
});
