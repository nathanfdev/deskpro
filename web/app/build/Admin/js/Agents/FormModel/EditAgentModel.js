(function() {
  define(['DeskPRO/Util/Strings'], function(Strings) {
    var EditAgentModel;
    return EditAgentModel = (function() {
      function EditAgentModel(agent, groups, teams) {
        var check, email, enabled, g, t, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _m, _ref, _ref1, _ref2, _ref3;
        this.form = {};
        this.form.name = agent.name;
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
        this.form.emails_list = [];
        this.form.email_primary = ((_ref = agent.primary_email) != null ? _ref.email : void 0) || '';
        if (agent && agent.emails && agent.emails.length) {
          _ref1 = agent.emails;
          for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
            email = _ref1[_i];
            this.form.emails_list.push(email.email);
          }
        }
        this.form.teams = [];
        for (_j = 0, _len1 = teams.length; _j < _len1; _j++) {
          t = teams[_j];
          enabled = false;
          _ref2 = agent.teams;
          for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
            check = _ref2[_k];
            if (check.id === t.id) {
              enabled = true;
              break;
            }
          }
          this.form.teams.push({
            id: t.id,
            name: t.name,
            value: enabled
          });
        }
        this.form.agent_groups = [];
        for (_l = 0, _len3 = groups.length; _l < _len3; _l++) {
          g = groups[_l];
          enabled = false;
          _ref3 = agent.usergroups;
          for (_m = 0, _len4 = _ref3.length; _m < _len4; _m++) {
            check = _ref3[_m];
            if (check.id === g.id) {
              enabled = true;
              break;
            }
          }
          this.form.agent_groups.push({
            id: g.id,
            title: g.title,
            value: enabled
          });
        }
      }

      EditAgentModel.prototype.getFormData = function() {
        var formData, g, primary_email, t, _i, _j, _len, _len1, _ref, _ref1;
        formData = {};
        formData.name = this.form.name;
        if (this.form.enable_display_name && Strings.trim(this.form.override_name)) {
          formData.override_name = Strings.trim(this.form.override_name);
        } else {
          formData.override_name = '';
        }
        formData.emails = this.form.emails_list;
        primary_email = this.form.email_primary;
        formData.emails.sort(function(a, b) {
          if (a === primary_email) {
            return -1;
          }
          if (b === primary_email) {
            return 1;
          }
          return 0;
        });
        formData.zones = [];
        if (this.form.zones.admin) {
          formData.zones.push('admin');
        }
        if (this.form.zones.reports) {
          formData.zones.push('reports');
        }
        formData.teams = [];
        _ref = this.form.teams;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          t = _ref[_i];
          if (t.value) {
            formData.teams.push(t.id);
          }
        }
        formData.agent_groups = [];
        _ref1 = this.form.agent_groups;
        for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
          g = _ref1[_j];
          if (g.value) {
            formData.agent_groups.push(g.id);
          }
        }
        return formData;
      };

      return EditAgentModel;

    })();
  });

}).call(this);

//# sourceMappingURL=EditAgentModel.js.map
