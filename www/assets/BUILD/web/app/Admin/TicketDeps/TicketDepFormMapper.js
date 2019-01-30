// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS203: Remove `|| {}` from converted for-own loops
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Model/DepAgentPermMatrix',
  'DeskPRO/Util/Util'
], function(
  DepAgentPermMatrix,
  Util
) {
  let TicketDepFormMapper;
  return (TicketDepFormMapper = class TicketDepFormMapper {
    getFormFromModel(dep, trigger, layouts, depPerms, agents, brands, agentgroups, usergroups, email_accounts) {
      const form = {
        title: '',
        user_title: '',
        parent_id: '0',
        enable_user_title: false,
        default_layout: {},
        custom_layout: {},
        brands: [],
        use_custom_layout: false,
        trigger_actions: {
          SetEmailAccount: {
            options: {
              email_account_id: '0'
            }
          },
          SendUserNewEmail: {
            enabled: false,
            options: {}
          }
        }
      };

      if (dep.id) {
        form.title = dep.title;
        form.brands = dep.brands;

        if (!Util.isBlank(dep.user_title)) {
          form.user_title = dep.user_title;
          form.enable_user_title = true;
        }

        if (!Util.isBlank(dep.parent_id)) {
          form.parent_id = dep.parent_id+'';
        }
      } else {
        for (let brand of Array.from(brands)) {
          form.brands.push(brand.id);
        }
      }

      if (email_accounts.length) {
        form.trigger_actions.SetEmailAccount.options.email_account_id = email_accounts[0].id+'';
      }

      if (trigger && __guard__(trigger.actions != null ? trigger.actions.actions : undefined, x => x.length)) {
        for (let act of Array.from(trigger.actions.actions)) {
          if (act.type === 'SetEmailAccount') {
            form.trigger_actions.SetEmailAccount.options = act.options;
          } else if (act.type === 'SendUserNewEmail') {
            form.trigger_actions.SendUserNewEmail.enabled = true;
            form.trigger_actions.SendUserNewEmail.options = act.options;

            if (['helpdesk_name', 'site_name', 'performer'].indexOf(form.trigger_actions.SendUserNewEmail.options.from_name) === -1) {
              form.trigger_actions.SendUserNewEmail.options.from_name_custom = form.trigger_actions.SendUserNewEmail.options.from_name;
              form.trigger_actions.SendUserNewEmail.options.from_name = 'custom';
            }
          }
        }
      }

      if (!form.trigger_actions.SendUserNewEmail.enabled) {
        form.trigger_actions.SendUserNewEmail.options = {
          template: 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
          from_name: 'helpdesk_name'
        };
      }

      form.default_layout = {
        agent: layouts.default_layout.agent.fields,
        user:  layouts.default_layout.user.fields,
      };

      if (layouts.custom_layout && layouts.use_custom_layout) {
        form.custom_layout = {
          agent: layouts.custom_layout.agent.fields,
          user:  layouts.custom_layout.user.fields,
        };
        form.use_custom_layout = true;
      } else {
        form.custom_layout = Util.clone(form.default_layout, true);
      }

      let depAgentGroupPerms = depPerms.agentgroups;
      if (!dep.id) {
        depAgentGroupPerms = [];
      }

      const matrix = new DepAgentPermMatrix();
      for (let group of Array.from(agentgroups)) {
        matrix.addGroup(group, []);

        // initialize new deps with all perms
        if (!dep.id) {
          depAgentGroupPerms.push({ usergroup_id: group.id, perm_name: 'full'});
        }
      }
      for (let agent of Array.from(agents)) {
        matrix.addAgent(agent, []);
      }

      matrix.initPerms(depAgentGroupPerms, depPerms.agents);
      form.agent_perms = matrix;

      form.usergroup_perms = {};
      for (let u of Array.from(usergroups)) {
        // if the dep exists, initialize default to false because real perms are applied below
        if (dep.id) {
          form.usergroup_perms[u.id] = { full: false };

        // new deps, default perms to on
        } else {
          form.usergroup_perms[u.id] = { full: true };
        }
      }

      if (depPerms.usergroups) {
        for (let p of Array.from(depPerms.usergroups)) {
          if ((form.usergroup_perms[p.usergroup_id] == null)) {
            form.usergroup_perms[p.usergroup_id] = {};
          }

          form.usergroup_perms[p.usergroup_id][p.perm_name] = true;
        }
      }

      return form;
    }


    getPostDataFromForm(formModel) {
      const depData = {};

      depData.title           = formModel.title;
      depData.parent          = formModel.parent_id || "0";
      depData.email_gateway   = formModel.email_gateway_id || "0";
      depData.move_tickets_to = 'self';
      depData.avatar          = formModel.avatar;
      depData.brands          = formModel.brands;

      if (Util.isBlank(depData.parent)) {
        depData.parent = null;
      }
      if (Util.isBlank(depData.email_gateway)) {
        depData.email_gateway = null;
      }

      if (formModel.enable_user_title) {
        depData.user_title = formModel.user_title;
      }

      let permData = [];
      if (typeof formModel.agent_perms.getPermsData === 'function') {
        permData = formModel.agent_perms.getPermsData();
      }
      for (let uid of Object.keys(formModel.usergroup_perms || {})) {
        const usergroup = formModel.usergroup_perms[uid];
        if (usergroup.full) {
          permData.push({
            usergroup_id: uid,
            name: 'full',
            value: 1
          });
        }
      }

      const trigger_actions = [];
      const email_account_id = parseInt(__guard__(formModel.trigger_actions.SetEmailAccount != null ? formModel.trigger_actions.SetEmailAccount.options : undefined, x => x.email_account_id) || 0);
      if (email_account_id) {
        trigger_actions.push({
          type: 'SetEmailAccount',
          options: {
            email_account_id
          }
        });
      }
      if (formModel.trigger_actions.SendUserNewEmail != null ? formModel.trigger_actions.SendUserNewEmail.enabled : undefined) {
        const { options } = formModel.trigger_actions.SendUserNewEmail;

        trigger_actions.push({
          type: 'SendUserNewEmail',
          options: {
            template:  options.template || 'DeskPRO:emails_user:ticket-new-autoreply.html.twig',
            from_name: options.from_name === 'custom' ? (options.from_name_custom || '') : (options.from_name || ''),
            do_cc_users: true,
            from_account: 0
          }
        });
      }

      const postData = {
        department: depData,
        trigger_actions,
        permissions: permData
      };

      return postData;
    }


    applyFormToModel(dep, formModel) {
      dep.title = formModel.title;

      if ((dep.display_order == null)) {
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
    }
  });
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}