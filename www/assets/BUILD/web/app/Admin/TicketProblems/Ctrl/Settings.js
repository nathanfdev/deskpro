/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_TicketProblems_Ctrl_Settings extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.updateAgents = this.updateAgents.bind(this);
      super(...args);
    }

    static initClass() {
  
      this.CTRL_ID = 'Admin_TicketProblems_Ctrl_Settings';
      this.CTRL_AS = 'Settings';
      this.DEPS = [];
    }

    init() {
      this.map = {}; // groups map
      this.service = this.DataService.get('Problems');
      this.$scope.settings = null;
      this.$scope.updateAgents = this.updateAgents;
      this.$scope.perm = {selected: 'view'};

      return this.$scope.$watch('settings.enabled', (newVal, oldVal) => {
        if (parseInt(newVal)) { return this.$scope.updateAgents(); }
      });
    }


    initialLoad() {
      return this.service.load().then(settings => {
        this.$scope.settings = settings;
        settings.groups.map(group => { return this.map[group.id] = group; });
        return settings.groups.sort((a, b) => {
          if (b.sys_name === 'agent_all_perms') { return 1; }
          if (a.sys_name === 'agent_all_perms') { return -1; }
          if (b.sys_name === 'agent_all_safe_perms') { return 1; }
          if (a.sys_name === 'agent_all_safe_perms') { return -1; }
          a = (a.sys_name || a.title).toLowerCase();
          b = (b.sys_name || b.title).toLowerCase();
          return a.localeCompare(b);
        });
      });
    }



    // update agents checkboxes states
    updateAgents() {

      const perm = this.$scope.perm.selected;
      return this.$scope.settings.agents.map(agent => {

        for (let group of Array.from(agent.usergroups)) {

          if (this.map[group.id] != null ? this.map[group.id].perms.problems[perm] : undefined) {
            agent[perm + '_checked'] = true;
            agent[perm + '_disabled'] = true;
            return;
          }
        }

        agent[perm + '_checked'] = agent.perms.problems[perm];
        return agent[perm + '_disabled'] = false;
      });
    }



    save() {
      this.startSpinner('saving');

      const perm = this.$scope.perm.selected;
      this.$scope.settings.agents.map(function(agent) {
        if (agent[perm + '_disabled']) { return; }
        return agent.perms.problems[perm] = agent[perm + '_checked'];});

      return this.service.save().then(
        () => {
          return this.stopSpinner('saving');
        },
        () => {
          return this.stopSpinner('saving');
      });
    }
  }
  Admin_TicketProblems_Ctrl_Settings.initClass();





  return Admin_TicketProblems_Ctrl_Settings.EXPORT_CTRL();
});