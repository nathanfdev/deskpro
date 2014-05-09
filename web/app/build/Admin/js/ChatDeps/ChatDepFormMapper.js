(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['Admin/Main/Model/DepAgentPermMatrix', 'DeskPRO/Util/Util'], function(DepAgentPermMatrix, Util) {
    var ChatDepFormMapper;
    return ChatDepFormMapper = (function() {
      function ChatDepFormMapper() {}


      /*
      		 *
       */

      ChatDepFormMapper.prototype.getFormFromModel = function(dep, depPerms, agents, agentgroups, usergroups) {
        var agent, form, group, matrix, p, u, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref;
        form = {
          title: '',
          user_title: '',
          parent_id: '0',
          enable_user_title: false
        };
        if (dep.id) {
          form.title = dep.title;
          if (!Util.isBlank(dep.user_title)) {
            form.user_title = dep.user_title;
            form.enable_user_title = true;
          }
          if (!Util.isBlank(dep.parent_id)) {
            form.parent_id = dep.parent_id + "";
          }
        }
        matrix = new DepAgentPermMatrix();
        for (_i = 0, _len = agentgroups.length; _i < _len; _i++) {
          group = agentgroups[_i];
          matrix.addGroup(group, []);
        }
        for (_j = 0, _len1 = agents.length; _j < _len1; _j++) {
          agent = agents[_j];
          matrix.addAgent(agent, []);
        }
        matrix.initPerms(depPerms.agentgroups, depPerms.agents);
        form.agent_perms = matrix;
        form.usergroup_perms = {};
        for (_k = 0, _len2 = usergroups.length; _k < _len2; _k++) {
          u = usergroups[_k];
          form.usergroup_perms[u.id] = {
            full: false
          };
        }
        if (depPerms.usergroups) {
          _ref = depPerms.usergroups;
          for (_l = 0, _len3 = _ref.length; _l < _len3; _l++) {
            p = _ref[_l];
            if (form.usergroup_perms[p.usergroup_id] == null) {
              form.usergroup_perms[p.usergroup_id] = {};
            }
            form.usergroup_perms[p.usergroup_id][p.perm_name] = true;
          }
        }
        return form;
      };


      /*
       	 *
       */

      ChatDepFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var depData, permData, postData, uid, usergroup, _ref;
        depData = {};
        depData.title = formModel.title;
        depData.parent = formModel.parent_id || "0";
        depData.move_tickets_to = 'self';
        if (Util.isBlank(depData.parent)) {
          depData.parent = null;
        }
        if (formModel.enable_user_title) {
          depData.user_title = formModel.user_title;
        }
        permData = formModel.agent_perms.getPermsData();
        _ref = formModel.usergroup_perms;
        for (uid in _ref) {
          if (!__hasProp.call(_ref, uid)) continue;
          usergroup = _ref[uid];
          if (usergroup.full) {
            permData.push({
              usergroup_id: uid,
              name: 'full',
              value: 1
            });
          }
        }
        postData = {
          department: depData,
          permissions: permData
        };
        return postData;
      };


      /*
      		 *
       */

      ChatDepFormMapper.prototype.applyFormToModel = function(dep, formModel) {
        dep.title = formModel.title;
        if (dep.display_order == null) {
          dep.display_order = 0;
        }
        if (Util.isBlank(formModel.parent_id)) {
          return dep.parent_id = null;
        } else {
          return dep.parent_id = parseInt(formModel.parent_id);
        }
      };

      return ChatDepFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=ChatDepFormMapper.js.map
