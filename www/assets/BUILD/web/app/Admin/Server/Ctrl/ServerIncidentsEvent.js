/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_ServerIncidents_Ctrl_Event extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerIncidents_Ctrl_Event';
      this.CTRL_AS   = 'View';
      this.DEPS      = ['Api2', '$sce'];
    }

    init() {
      this.event = {};
      return this.instructions_html = '';
    }

    initialLoad() {
      return this.Api2.sendGet(`/system/events/${this.$stateParams.id}`).then(
        ({data}) => { return this.event = data.data;
       });
    }
  }
  Admin_ServerIncidents_Ctrl_Event.initClass();

  return Admin_ServerIncidents_Ctrl_Event.EXPORT_CTRL();
});
