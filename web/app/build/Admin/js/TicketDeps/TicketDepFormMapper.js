(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['Admin/Main/Model/DepAgentPermMatrix', 'DeskPRO/Util/Util'], function(DepAgentPermMatrix, Util) {
    var TicketDepFormMapper;
    return TicketDepFormMapper = (function() {
      function TicketDepFormMapper() {}

      TicketDepFormMapper.prototype.getFormFromModel = function(dep, trigger, layouts, depPerms, agents, agentgroups, usergroups, email_accounts) {
        var act, agent, depAgentGroupPerms, form, group, matrix, p, u, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _m, _ref, _ref1, _ref2, _ref3;
        form = {
          title: '',
          user_title: '',
          parent_id: '0',
          enable_user_title: false,
          default_layout: {},
          custom_layout: {},
          use_custom_layout: false,
          trigger_actions: {
            SetEmailAccount: {
              options: {
                email_account_id: '0'
              }
            },
            SendUserEmail: {
              enabled: false,
              options: {}
            }
          }
        };
        if (dep.id) {
          form.title = dep.title;
          if (!Util.isBlank(dep.user_title)) {
            form.user_title = dep.user_title;
            form.enable_user_title = true;
          }
          if (!Util.isBlank(dep.parent_id)) {
            form.parent_id = dep.parent_id + '';
          }
        }
        if (email_accounts.length) {
          form.trigger_actions.SetEmailAccount.options.email_account_id = email_accounts[0].id + '';
        }
        if (trigger && ((_ref = trigger.actions) != null ? (_ref1 = _ref.actions) != null ? _ref1.length : void 0 : void 0)) {
          _ref2 = trigger.actions.actions;
          for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
            act = _ref2[_i];
            if (act.type === 'SetEmailAccount') {
              form.trigger_actions.SetEmailAccount.options = act.options;
            } else if (act.type === 'SendUserEmail') {
              form.trigger_actions.SendUserEmail.enabled = true;
              form.trigger_actions.SendUserEmail.options = act.options;
              if (['helpdesk_name', 'site_name'].indexOf(form.trigger_actions.SendUserEmail.options.from_name) === -1) {
                form.trigger_actions.SendUserEmail.options.from_name_custom = form.trigger_actions.SendUserEmail.options.from_name;
                form.trigger_actions.SendUserEmail.options.from_name = 'custom';
              }
            }
          }
        }
        if (!form.trigger_actions.SendUserEmail.enabled) {
          form.trigger_actions.SendUserEmail.options = {
            template: 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
            from_name: 'helpdesk_name'
          };
        }
        form.default_layout = {
          agent: layouts.default_layout.agent.fields,
          user: layouts.default_layout.user.fields
        };
        if (layouts.custom_layout && layouts.use_custom_layout) {
          form.custom_layout = {
            agent: layouts.custom_layout.agent.fields,
            user: layouts.custom_layout.user.fields
          };
          form.use_custom_layout = true;
        } else {
          form.custom_layout = Util.clone(form.default_layout, true);
        }
        depAgentGroupPerms = depPerms.agentgroups;
        if (!dep.id) {
          depAgentGroupPerms = [];
        }
        matrix = new DepAgentPermMatrix();
        for (_j = 0, _len1 = agentgroups.length; _j < _len1; _j++) {
          group = agentgroups[_j];
          matrix.addGroup(group, []);
          if (!dep.id) {
            depAgentGroupPerms.push({
              usergroup_id: group.id,
              perm_name: 'full'
            });
          }
        }
        for (_k = 0, _len2 = agents.length; _k < _len2; _k++) {
          agent = agents[_k];
          matrix.addAgent(agent, []);
        }
        matrix.initPerms(depAgentGroupPerms, depPerms.agents);
        form.agent_perms = matrix;
        form.usergroup_perms = {};
        for (_l = 0, _len3 = usergroups.length; _l < _len3; _l++) {
          u = usergroups[_l];
          if (dep.id) {
            form.usergroup_perms[u.id] = {
              full: false
            };
          } else {
            form.usergroup_perms[u.id] = {
              full: true
            };
          }
        }
        if (depPerms.usergroups) {
          _ref3 = depPerms.usergroups;
          for (_m = 0, _len4 = _ref3.length; _m < _len4; _m++) {
            p = _ref3[_m];
            if (form.usergroup_perms[p.usergroup_id] == null) {
              form.usergroup_perms[p.usergroup_id] = {};
            }
            form.usergroup_perms[p.usergroup_id][p.perm_name] = true;
          }
        }
        return form;
      };

      TicketDepFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var depData, email_account_id, options, permData, postData, trigger_actions, uid, usergroup, _ref, _ref1, _ref2, _ref3;
        depData = {};
        depData.title = formModel.title;
        depData.parent = formModel.parent_id || "0";
        depData.email_gateway = formModel.email_gateway_id || "0";
        depData.move_tickets_to = 'self';
        if (Util.isBlank(depData.parent)) {
          depData.parent = null;
        }
        if (Util.isBlank(depData.email_gateway)) {
          depData.email_gateway = null;
        }
        if (formModel.enable_user_title) {
          depData.user_title = formModel.user_titl;
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
        trigger_actions = [];
        email_account_id = parseInt(((_ref1 = formModel.trigger_actions.SetEmailAccount) != null ? (_ref2 = _ref1.options) != null ? _ref2.email_account_id : void 0 : void 0) || 0);
        if (email_account_id) {
          trigger_actions.push({
            type: 'SetEmailAccount',
            options: {
              email_account_id: email_account_id
            }
          });
        }
        if ((_ref3 = formModel.trigger_actions.SendUserEmail) != null ? _ref3.enabled : void 0) {
          options = formModel.trigger_actions.SendUserEmail.options;
          trigger_actions.push({
            type: 'SendUserEmail',
            options: {
              template: options.template || 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
              from_name: options.from_name === 'custom' ? options.from_name_custom || '' : options.from_name || '',
              do_cc_users: true,
              from_account: 0
            }
          });
        }
        postData = {
          department: depData,
          trigger_actions: trigger_actions,
          permissions: permData
        };
        return postData;
      };

      TicketDepFormMapper.prototype.applyFormToModel = function(dep, formModel) {
        dep.title = formModel.title;
        if (dep.display_order == null) {
          dep.display_order = 0;
        }
        if (Util.isBlank(formModel.parent_id)) {
          dep.parent_id = null;
        } else {
          dep.parent_id = parseInt(formModel.parent_id);
        }
        if (formModel.use_custom_layout) {
          return dep.has_layout = true;
        } else {
          return dep.has_layout = false;
        }
      };

      return TicketDepFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=TicketDepFormMapper.js.map
