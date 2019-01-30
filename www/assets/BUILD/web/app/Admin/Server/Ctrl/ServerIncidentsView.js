// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerIncidents_Ctrl_View extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerIncidents_Ctrl_View';
      this.CTRL_AS   = 'View';
      this.DEPS      = ['Api2', '$sce'];
    }

    init() {
      this.incident = {};
      return this.instructions_html = '';
    }

    initialLoad() {
      return this.Api2.sendGet(`/system/incidents/${this.$stateParams.id}`).then(
        ({data}) => { this.incident = data.data.incident; return this.instructions_html = this.$sce.trustAsHtml(data.data.instructions_html);
       });
    }
  }
  Admin_ServerIncidents_Ctrl_View.initClass();

  return Admin_ServerIncidents_Ctrl_View.EXPORT_CTRL();
});
