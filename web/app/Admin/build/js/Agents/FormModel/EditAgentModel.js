(function() {
  define(['DeskPRO/Util/Strings'], function(Strings) {
    var EditAgentModel;
    return EditAgentModel = (function() {
      function EditAgentModel(agent, groups, teams) {
        var check, enabled, g, t, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref2;
        this.form = {};
        this.form.name = agent.name;
        if (agent.override_display_name) {
          this.form.enable_display_name = true;
          this.form.override_name = agent.enable_display_name;
        } else {
          this.form.enable_display_name = false;
          this.form.override_name = '';
        }
        this.form.email = (_ref = agent.primary_email) != null ? _ref.email : void 0;
        this.form.zones = {
          admin: agent.can_admin,
          reports: agent.can_reports
        };
        this.form.teams = [];
        for (_i = 0, _len = teams.length; _i < _len; _i++) {
          t = teams[_i];
          enabled = false;
          _ref1 = agent.teams;
          for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            check = _ref1[_j];
            if (check.id = t.id) {
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
        for (_k = 0, _len2 = groups.length; _k < _len2; _k++) {
          g = groups[_k];
          enabled = false;
          _ref2 = agent.usergroups;
          for (_l = 0, _len3 = _ref2.length; _l < _len3; _l++) {
            check = _ref2[_l];
            if (check.id = g.id) {
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
        var formData, g, t, _i, _j, _len, _len1, _ref, _ref1;
        formData = {};
        formData.name = this.form.name;
        if (this.form.enable_display_name && Strings.trim(this.form.override_name)) {
          formData.override_name = Strings.trim(this.form.override_name);
        } else {
          formData.override_name = '';
        }
        formData.email = this.form.email;
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
