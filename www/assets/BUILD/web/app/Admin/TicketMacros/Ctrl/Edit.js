define([
  'Admin/Main/Ctrl/Base'
], function(
  Admin_Ctrl_Base
) {
  class Admin_TicketMacros_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketMacros_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['dpObTypesDefTicketActions', '$stateParams'];
    }

    init() {
      this.macroData  = this.DataService.get('TicketMacros');
      this.$scope.me_id = window.DP_PERSON_ID;

      this.macroId = parseInt(this.$stateParams.id);
      this.permType = 'global';
      this.macro  = null;
      this.agents = null;
      this.departments = null;
      this.form   = null;
      this.form   = null;

      this.actionsTypeDef    = this.dpObTypesDefTicketActions;
      this.actionOptionTypes = this.actionsTypeDef.getOptionsForTypes();

      return this.$scope.$watch('EditCtrl.form.person_id', id => {
        id = parseInt(id);
        let name = 'unknown agent';
        if (id && !isNaN(id)) {
          const a = this.agents.filter(a => a.id === id);
          if (a[0]) { name = a[0].display_name; }
        }

        return this.$scope.for_agent_name = name;
      });
    }

    initialLoad() {
      const promises = [];
      let promise = this.macroData.loadEditMacroData(this.macroId || null).then( data => {
        this.macro  = data.macro;
        this.agents = data.agents;
        this.form   = data.form;

        switch (false) {
          case !this.form.is_global: return this.permType = 'global';
          case !this.form.person_id: return this.permType = 'agent';
          case !this.form.department_id: return this.permType = 'department';
        }
      });
      promises.push(promise);

      promise = this.Api2.sendGet('/ticket_departments?selectable=1');
      promise.then(res => {
        return this.departments = res.data.data;
      });
      promises.push(promise);

      return this.$q.all(promises);
    }

    changePermType() {
      this.form.is_global = 0;
      this.form.person_id = null;
      this.form.department_id = null;

      switch (this.permType) {
        case 'global': return this.form.is_global = 1;
        case 'agent': return this.form.person_id = this.agents[0].id;
        case 'department': return this.form.department_id = this.departments[0].id;
      }
    }

    saveForm() {
      this.form.agents = this.agents;
      this.form.departments = this.departments;
      const promise = this.macroData.saveFormModel(this.macro, this.form);

      this.startSpinner('saving');
      return promise.then( () => {
        this.stopSpinner('saving');

        this.skipDirtyState();
        if (!this.macroId) {
          return this.$state.go('tickets.macros.gocreate');
        }
      });
    }
  }
  Admin_TicketMacros_Ctrl_Edit.initClass();

  return Admin_TicketMacros_Ctrl_Edit.EXPORT_CTRL();
});