define(['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) => {
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
        ({ data }) => {
          this.incident = data.data.incident; return this.instructions_html = this.$sce.trustAsHtml(data.data.instructions_html);
        });
    }
  }
  Admin_ServerIncidents_Ctrl_View.initClass();

  return Admin_ServerIncidents_Ctrl_View.EXPORT_CTRL();
});
