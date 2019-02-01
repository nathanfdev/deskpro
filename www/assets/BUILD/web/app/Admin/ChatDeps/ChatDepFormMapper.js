define([
  'Admin/Main/Model/DepAgentPermMatrix',
  'DeskPRO/Util/Util'
], function(
  DepAgentPermMatrix,
  Util
) {
  class ChatDepFormMapper {

    /*
     *
     */

    getFormFromModel(dep, depPerms, agents, brands, agentgroups, usergroups) {
      const form = {
        title: '',
        user_title: '',
        parent_id: '0',
        chat_queue_id: '0',
        enable_user_title: false,
        brands: [],
      };

      if (dep.id) {
        form.title = dep.title;
        form.brands = dep.brands;

        if (!Util.isBlank(dep.user_title)) {
          form.user_title = dep.user_title;
          form.enable_user_title = true;
        }

        if (!Util.isBlank(dep.parent_id)) {
          form.parent_id = dep.parent_id + "";
        }
        if (!Util.isBlank(dep.chat_queue_id)) {
          form.chat_queue_id = dep.chat_queue_id + "";
        }
      } else {
        for (let brand of Array.from(brands)) {
          form.brands.push(brand.id);
        }
      }

      const matrix = new DepAgentPermMatrix();
      for (let group of Array.from(agentgroups)) {
        matrix.addGroup(group, []);
      }
      for (let agent of Array.from(agents)) {
        matrix.addAgent(agent, []);
      }

      matrix.initPerms(depPerms.agentgroups, depPerms.agents);
      form.agent_perms = matrix;

      form.usergroup_perms = {};
      for (let u of Array.from(usergroups)) {
        form.usergroup_perms[u.id] = { full: false };
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

    /*
  *
    */

    getPostDataFromForm(formModel) {
      let chatQueueId = formModel.chat_queue_id;
      if (!chatQueueId || (chatQueueId === "0")) {
        chatQueueId = null;
      }

      const depData = {};

      depData.title           = formModel.title;
      depData.parent          = formModel.parent_id || "0";
      depData.chat_queue      = chatQueueId;
      depData.move_tickets_to = 'self';
      depData.avatar          = formModel.avatar;
      depData.brands          = formModel.brands;

      if (Util.isBlank(depData.parent)) {
        depData.parent = null;
      }

      if (formModel.enable_user_title) {
        depData.user_title = formModel.user_title;
      }

      const permData = formModel.agent_perms.getPermsData();

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


      const postData = {
        department: depData,
        permissions: permData
      };

      return postData;
    }

    /*
     *
     */

    applyFormToModel(dep, formModel) {

      dep.title = formModel.title;

      if ((dep.display_order == null)) {
        dep.display_order = 0;
      }

      if (Util.isBlank(formModel.parent_id)) {
        return dep.parent_id = null;
      } else {
        return dep.parent_id = parseInt(formModel.parent_id);
      }
    }
  }

  return ChatDepFormMapper;
});