define([
  'Admin/CustomFields/Base/Ctrl/Edit',
], function(Admin_CustomFields_Base_Ctrl_Edit) {
  class Admin_CustomFields_Tickets_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit {
    static initClass() {
      this.CTRL_ID = 'Admin_CustomFields_Tickets_Ctrl_Edit';
      this.CTRL_AS = 'EditCtrl';
      this.DEPS = [];
    }

    init() {
      super.init(...arguments);
      this.showLayouts = false;
      this.referencedByApp = { status: false, appName: "", appUrl: "#" };
    }

    initialLoadExtra() {
      return this.Api.sendGet(`/ticket_layouts/fields/ticket_field_${this.field_id || '__undefined__'}`).success(data => {
        this.user_layouts = data.user_layouts;
        this.agent_layouts = data.agent_layouts;

        if (!this.field_id) {
          for (var l of Object.keys(this.user_layouts || {})) {
            this.user_layouts[l].enabled = true;
          }
          return (() => {
            const result = [];
            for (l of Object.keys(this.agent_layouts || {})) {
              result.push(this.agent_layouts[l].enabled = true);
            }
            return result;
          })();
        }

      });
    }

    postLoad(fieldData) {
      const referencedByAppFilter = reference => reference.entity === 'app';

      const referencingApps = fieldData.referencedBy instanceof Array ? fieldData.referencedBy.filter(referencedByAppFilter) : [];

      const isReferenced = referencingApps.length !== 0;
      this.showLayouts = !isReferenced;
      this.showFieldType = !isReferenced;
      this.showEnabled = !isReferenced;
      this.showAgentOnly = !isReferenced;

      this.referencedByApp  = {
        status: isReferenced,
        appName: isReferenced ? referencingApps[0].appName : '',
        appUrl: isReferenced ?  `apps/apps/v2_${referencingApps[0].appId}` : '#'
      };

    }

    startDelete() {
      if (this.referencedByApp.status) {
        this.showAlert('This field can not be deleted until the app has been deleted');
        return;
      }
      return super.startDelete(...arguments);
    }

    postSave() {
      const postData = {
        enable_user_layouts:  [],
        enable_agent_layouts: []
      };

      if (this.form.is_enabled) {
        let k, l;
        if (!this.form.is_agent_field) {
          for (k of Object.keys(this.user_layouts || {})) {
            l = this.user_layouts[k];
            if (l.enabled) {
              postData.enable_user_layouts.push(l.department ? l.department.id : 0);
            }
          }
        }
        for (k of Object.keys(this.agent_layouts || {})) {
          l = this.agent_layouts[k];
          if (l.enabled) {
            postData.enable_agent_layouts.push(l.department ? l.department.id : 0);
          }
        }
      }

      return this.Api.sendPostJson(`/ticket_layouts/fields/ticket_field_${this.field_id}`, postData);
    }

    getDataService() {
      return this.DataService.get('TicketFields');
    }

    getBaseRouteName() {
      return "tickets.fields";
    }

    type() {
      return 'tickets';
    }
  }
  Admin_CustomFields_Tickets_Ctrl_Edit.initClass();

  return Admin_CustomFields_Tickets_Ctrl_Edit.EXPORT_CTRL();
});
