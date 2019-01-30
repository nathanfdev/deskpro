/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketFields_Ctrl_List extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_TicketFields_Ctrl_List';
      this.CTRL_AS = 'TicketFieldsList';
      this.DEPS    = [];
    }

    init() {
      this.ticket_fields = this.DataService.get('TicketFields');
      this.custom_fields = [];
      this.field_enabled = {};
    }

    initialLoad() {
      const promise = this.ticket_fields.loadList();
      return promise.then(list => {
        this.custom_fields = list;
        return this.field_enabled = this.ticket_fields.field_enabled;
      });
    }

    setFieldEnabled(id, is_enabled) {
      return this.field_enabled[id] = is_enabled;
    }

    saveLayoutData(id, user_layouts, agent_layouts) {
      let l;
      const postData = {
        enable_user_layouts: [],
        enable_agent_layouts: []
      };

      for (var k of Object.keys(user_layouts || {})) {
        l = user_layouts[k];
        if (l.enabled) {
          postData.enable_user_layouts.push(l.department ? l.department.id : 0);
        }
      }
      for (k of Object.keys(agent_layouts || {})) {
        l = agent_layouts[k];
        if (l.enabled) {
          postData.enable_agent_layouts.push(l.department ? l.department.id : 0);
        }
      }

      return this.Api.sendPostJson(`/ticket_layouts/fields/${id}`, postData);
    }
  }
  Admin_TicketFields_Ctrl_List.initClass();

  return Admin_TicketFields_Ctrl_List.EXPORT_CTRL();
});