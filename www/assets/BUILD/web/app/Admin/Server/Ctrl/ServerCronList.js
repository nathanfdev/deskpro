// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerCron_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
  
      this.CTRL_ID   = 'Admin_ServerCron_Ctrl_List';
      this.CTRL_AS   = 'ListCtrl';
      this.DEPS      = [];
    }

    init() {
      return this.server_cron = null;
    }

    initialLoad() {
      const data_promise = this.Api.sendGet('/server_cron').then( res => {

        return this.server_cron = res.data.server_cron;
      });

      return this.$q.all([data_promise]);
    }
  }
  Admin_ServerCron_Ctrl_List.initClass();

  return Admin_ServerCron_Ctrl_List.EXPORT_CTRL();
});