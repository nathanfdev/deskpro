// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
  class Admin_ServerJobs_Ctrl_View extends Admin_Ctrl_Base {
    static initClass() {
  
      this.CTRL_ID   = 'Admin_ServerJobs_Ctrl_View';
      this.CTRL_AS   = 'JobsViewCtrl';
      this.DEPS      = ['$stateParams'];
    }

    init() {
      this.service = this.DataService.get('Jobs');
      return this.job =
        {id: this.$stateParams.id};
    }

    initialLoad() {
      return this.service.get(this.$stateParams.id).then(data => {
        this.job = data;
        return this.service.loadJob(this.job.id).then(data => {
          return this.job = data;
        });
      });
    }

    getData() {
      return angular.toJson(this.job.data, true);
    }
  }
  Admin_ServerJobs_Ctrl_View.initClass();


  return Admin_ServerJobs_Ctrl_View.EXPORT_CTRL();
});
