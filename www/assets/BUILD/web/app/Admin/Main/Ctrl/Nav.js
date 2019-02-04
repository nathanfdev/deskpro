define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Main_Ctrl_Nav extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_Nav';
      this.DEPS = ['$timeout'];
    }

    init() {
      const depth = this.$state.current.name.split('.').length;
      if (depth === 1) {
        this.$timeout(() => $('.dp-layout-appnav').find('li').first().find('a').click()
        , 10);
      }
    }
  }
  Admin_Main_Ctrl_Nav.initClass();

  return Admin_Main_Ctrl_Nav.EXPORT_CTRL();
});
