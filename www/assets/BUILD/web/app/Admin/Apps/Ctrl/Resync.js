define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
  class Admin_Apps_Ctrl_Resync extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Apps_Ctrl_Resync';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = [];
    }

    init() {
      this.$scope.state = 'default';
    }

    initialLoad() {
    }

    beginResync() {
      this.$scope.state = 'running';
      return this.Api.sendPost('/apps/resync-packages').then((result) => {
        this.$scope.state = 'done';
        return this.$scope.log = result.data.log;
      }
      , (result) => {
        this.$scope.state = 'done';
        return this.$scope.log = 'There was an error. Please try again.';
      });
    }
  }
  Admin_Apps_Ctrl_Resync.initClass();

  return Admin_Apps_Ctrl_Resync.EXPORT_CTRL();
});
