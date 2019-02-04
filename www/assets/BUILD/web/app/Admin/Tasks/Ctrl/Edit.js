define([
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) => {
  class Admin_Tasks_Ctrl_Edit extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        const thisFn = (() => this).toString();
        const thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.updateAgents = this.updateAgents.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID   = 'Admin_Tasks_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['$stateParams'];
    }

    init() {
      this.map = {}; // groups map
      this.service = this.DataService.get('Tasks');
      this.$scope.settings = null;
      this.$scope.updateAgents = this.updateAgents;

      return this.$scope.$watch('settings', (newVal, oldVal) => {
        if (parseInt(newVal != null ? newVal.enabled : undefined)) { return this.$scope.updateAgents(); }
      });
    }


    initialLoad() {
      return this.service.load().then((settings) => {
        this.$scope.settings = settings;
        return settings.groups.map(group => this.map[group.id] = group);
      });
    }


    // update agents checkboxes states
    updateAgents() {
      return this.$scope.settings.agents.map((agent) => {
        for (const group of Array.from(agent.usergroups)) {
          if (this.map[group.id] != null ? this.map[group.id].perms.tasks.use : undefined) {
            agent._checked = true;
            agent._disabled = true;
            return;
          }
        }

        agent._checked = agent.perms.tasks.use;
        return agent._disabled = false;
      });
    }


    save() {
      this.startSpinner('saving');

      for (const agent of Array.from(this.$scope.settings.agents)) {
        if (!agent._disabled) { agent.perms.tasks.use = agent._checked; }
      }

      return this.service.save().then(
        () => this.stopSpinner('saving'),
        () => this.stopSpinner('saving'));
    }
  }
  Admin_Tasks_Ctrl_Edit.initClass();


  return Admin_Tasks_Ctrl_Edit.EXPORT_CTRL();
});
