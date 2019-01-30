/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Strings'], function(Strings) {
  let EditAgentModel;
  return (EditAgentModel = class EditAgentModel {
    constructor(agent, groups, teams, primary_phone_number_region) {
      let check, enabled;
      this.form = {};
      //--------------------
      // Basic props
      //--------------------

      this.form.name = agent.name;
      if (agent.primary_phone) {
        this.form.primary_phone = {
          region: agent.primary_phone.region || primary_phone_number_region,
          number: agent.primary_phone.number || '',
          ext: agent.primary_phone.ext || ''
        };
      } else {
        this.form.primary_phone = {
          region:  primary_phone_number_region,
          number:  '',
          ext: ''
        };
      }
      this.form.primary_team = agent.primary_team;
      this.form.notification_settings = agent.notification_settings;


      if (agent.override_display_name) {
        this.form.enable_display_name = true;
        this.form.override_name = agent.override_display_name;
      } else {
        this.form.enable_display_name = false;
        this.form.override_name = '';
      }

      this.form.zones = {
        admin: agent.can_admin,
        reports: agent.can_reports
      };

      //--------------------
      // Emails
      //--------------------

      this.form.emails_list = [];
      this.form.email_primary = (agent.primary_email != null ? agent.primary_email.email : undefined) || '';

      if (agent && agent.emails && agent.emails.length) {
        for (let email of Array.from(agent.emails)) {
          this.form.emails_list.push(email.email);
        }
      }

      //--------------------
      // Teams
      //--------------------

      this.form.teams = [];
      for (let t of Array.from(teams)) {
        enabled = false;
        for (check of Array.from(agent.teams)) {
          if (check.id === t.id) {
            enabled = true;
            break;
          }
        }

        this.form.teams.push({
          id:    t.id,
          name:  t.name,
          value: enabled
        });
      }

      //--------------------
      // Groups
      //--------------------

      this.form.agent_groups = [];
      for (let g of Array.from(groups)) {
        enabled = false;
        for (check of Array.from(agent.usergroups)) {
          if (check.id === g.id) {
            enabled = true;
            break;
          }
        }
        if (!enabled && !agent.id && (g.sys_name === 'agent_all_perms')) {
          enabled = true;
        }

        this.form.agent_groups.push({
          id:    g.id,
          title: g.title,
          value: enabled
        });
      }
    }

    getFormData() {
      const formData = {};
      formData.name = this.form.name;
      formData.primary_phone = {number: this.form.primary_phone.number, ext: this.form.primary_phone.ext};
      if (!formData.primary_phone.number) {
        formData.primary_phone = null;
      }
      formData.notification_settings = this.form.notification_settings;
      formData.primary_team = this.form.primary_team ? this.form.primary_team.id : null;

      if (this.form.enable_display_name && Strings.trim(this.form.override_name)) {
        formData.override_name = Strings.trim(this.form.override_name);
      } else {
        formData.override_name = '';
      }

      formData.emails = this.form.emails_list;
      const primary_email = this.form.email_primary;

      // the primary email goes first
      formData.emails.sort( function(a, b) {
        if (a === primary_email) { return -1; }
        if (b === primary_email) { return 1; }
        return 0;
      });

      formData.zones = [];
      if (this.form.zones.admin) {   formData.zones.push('admin'); }
      if (this.form.zones.reports) { formData.zones.push('reports'); }

      formData.teams = [];
      for (let t of Array.from(this.form.teams)) {
        if (t.value) {
          formData.teams.push(t.id);
        }
      }

      formData.agent_groups = [];
      for (let g of Array.from(this.form.agent_groups)) {
        if (g.value) {
          formData.agent_groups.push(g.id);
        }
      }

      return formData;
    }
  });
});
