// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerJobs_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
  
      this.CTRL_ID   = 'Admin_ServerJobs_Ctrl_List';
      this.CTRL_AS   = 'JobsListCtrl';
      this.DEPS      = ['Api2'];
    }

    init() {
      this.service = this.DataService.get('Jobs');
      return this.pagination =
        {page: 1};
    }

    initialLoad() {
      return this.service.loadList(null, {page: 1}).then( data => {
        this.jobs = data;
        return this.pagination = this.service.getPagination();
      });
    }
  }
  Admin_ServerJobs_Ctrl_List.initClass();

  return Admin_ServerJobs_Ctrl_List.EXPORT_CTRL();
});