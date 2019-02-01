define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerTaskQueue_Ctrl_ServerTaskQueue extends Admin_Ctrl_Base {
    static initClass() {
  
      this.CTRL_ID   = 'Admin_ServerTaskQueue_Ctrl_ServerTaskQueue';
      this.CTRL_AS   = 'ServerTaskQueue';
      this.DEPS      = [];
    }

    init() {
      return this.$scope.server_task_queue = null;
    }

    initialLoad() {
      const data_promise = this.Api.sendGet('/server_task_queue').then( res => {

        return this.$scope.server_task_queue = res.data.server_task_queue;
      });

      return this.$q.all([data_promise]);
    }
  }
  Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.initClass();

  return Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.EXPORT_CTRL();
});