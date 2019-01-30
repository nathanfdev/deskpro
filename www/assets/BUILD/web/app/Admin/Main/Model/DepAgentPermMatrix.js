/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  let Admin_Main_Model_DepAgentPermMatrix;
  return (Admin_Main_Model_DepAgentPermMatrix = class Admin_Main_Model_DepAgentPermMatrix {
    constructor() {
      this.agents = [];
      this.groups = [];

      this.agents_map = {};
      this.groups_map = {};
    }


    /**
    * Add a group
      *
      * @param {Model}  group        The group model
      * @param {Array}  perms_array  Array of current perm values
    */
    addGroup(group, perms_array) {

      const perms = {};
      for (let p of Array.from(perms_array)) {
        perms[p.name] = { state: false, set_state: p.state, soft_state: false, locked: false };
      }

      const obj = {
        type:  'group',
        model: group,
        perms,
        aids:  []
      };

      this.groups.push(obj);
      return this.groups_map[group.id] = obj;
    }


    /**
    * Add an agnet
      *
      * @param {Model}  agent        The agent model
      * @param {Array}  perms_array  Array of current perm values
    */
    addAgent(agent, perms_array) {

      const perms = {};
      for (let p of Array.from(perms_array)) {
        perms[p.name] = { state: false, set_state: p.state, soft_state: true, locked: false };
      }

      const obj = {
        type:  'agent',
        model: agent,
        perms
      };

      this.agents.push(obj);
      return this.agents_map[agent.id] = obj;
    }



    /**
    * After all perm values are added to the matrix,
      * this should be called to propogate values from ug's to
      * agents and set the proper locked state.
    */
    initPerms(group_perms, agent_perms) {

      let p;
      if (group_perms) {
        for (p of Array.from(group_perms)) {
          if ((this.groups_map[p.usergroup_id] == null)) { continue; }
          if ((this.groups_map[p.usergroup_id].perms[p.perm_name] == null)) {
            this.groups_map[p.usergroup_id].perms[p.perm_name] = { state: false, set_state: false, soft_state: false, locked: false };
          }

          this.groups_map[p.usergroup_id].perms[p.perm_name].set_state = true;
        }
      }

      if (agent_perms) {
        for (p of Array.from(agent_perms)) {
          if ((this.agents_map[p.agent_id] == null)) { continue; }
          if ((this.agents_map[p.agent_id].perms[p.perm_name] == null)) {
            this.agents_map[p.agent_id].perms[p.perm_name] = { state: false, set_state: false, soft_state: false, locked: false };
          }

          this.agents_map[p.agent_id].perms[p.perm_name].set_state = true;
        }
      }

      // Init group_to_agents map
      // And fill/correct missing perms
      for (let agentObj of Array.from(this.agents)) {
        const agent = agentObj.model;
        const agentPerms = agentObj.perms;

        if (!agent.agentgroup_ids) { continue; }

        for (let gid of Array.from(agent.agentgroup_ids)) {
          if (!this.groups_map[gid]) { continue; }
          this.groups_map[gid].aids.push(agent.id);
        }

        if ((agentPerms.full == null)) {   agentPerms.full   = { state: false, set_state: false, soft_state: false, locked: false }; }
        if ((agentPerms.assign == null)) { agentPerms.assign = { state: false, set_state: false, soft_state: false, locked: false }; }

        if (agentPerms.full.set_state || agentPerms.full.soft_state) {
          agentPerms.full.state = true;
        } else {
          agentPerms.full.state = false;
        }

        if (agentPerms.full.state) {
          agentPerms.assign.soft_state  = true;
          agentPerms.assign.locked      = true;
        }

        if (agentPerms.assign.set_state || agentPerms.assign.soft_state) {
          agentPerms.assign.state = true;
        } else {
          agentPerms.assign.state = false;
        }
      }

      // Fill/correct usergroup perms
      // And propogate values to agents
      return (() => {
        const result = [];
        for (var groupObj of Array.from(this.groups)) {
          const group = groupObj.model;
          var groupPerms = groupObj.perms;

          if ((groupPerms.full == null)) {   groupPerms.full   = { state: false, set_state: false, soft_state: false, locked: false }; }
          if ((groupPerms.assign == null)) { groupPerms.assign = { state: false, set_state: false, soft_state: false, locked: false }; }

          if (groupPerms.full.set_state || groupPerms.full.soft_state) {
            groupPerms.full.state = true;
          } else {
            groupPerms.full.state = false;
          }

          if (groupPerms.full.state) {
            groupPerms.assign.soft_state  = true;
            groupPerms.assign.locked      = true;
          }

          if (groupPerms.assign.set_state || groupPerms.assign.soft_state) {
            groupPerms.assign.state = true;
          } else {
            groupPerms.assign.state = false;
          }

          if (groupPerms.full.state || groupPerms.assign.state) {
            result.push((() => {
              const result1 = [];
              for (let aid of Array.from(groupObj.aids)) {
                if (groupPerms.full.state) {
                  this.agents_map[aid].perms.full.soft_state   = true;
                  this.agents_map[aid].perms.full.state        = true;
                  this.agents_map[aid].perms.full.locked       = true;
                  this.agents_map[aid].perms.assign.soft_state = true;
                  this.agents_map[aid].perms.assign.state      = true;
                  result1.push(this.agents_map[aid].perms.assign.locked     = true);
                } else if (groupPerms.assign) {
                  this.agents_map[aid].perms.assign.soft_state  = true;
                  this.agents_map[aid].perms.assign.state       = true;
                  result1.push(this.agents_map[aid].perms.assign.locked      = true);
                } else {
                  result1.push(undefined);
                }
              }
              return result1;
            })());
          } else {
            result.push(undefined);
          }
        }
        return result;
      })();
    }


    /**
    * Refreshes permissions on agents based on current group permssions
    */
    refreshAgentGroupPerms(aid = null) {

      let agents;
      if (aid) {
        agents = [this.agents_map[aid]];
      } else {
        ({ agents } = this);
      }

      return (() => {
        const result = [];
        for (let agentObj of Array.from(agents)) {
          const agent = agentObj.model;
          const agentPerms = agentObj.perms;

          // Reset soft/locked state
          agentPerms.full.soft_state   = false;
          agentPerms.full.locked       = false;
          agentPerms.assign.soft_state = false;
          agentPerms.assign.locked     = false;

          // Then process usergroups on them
          for (let gid of Array.from(agent.agentgroup_ids)) {
            const groupPerms = this.groups_map[gid].perms;

            if (groupPerms.full.state) {
              agentPerms.full.soft_state   = true;
              agentPerms.full.locked       = true;
              agentPerms.assign.soft_state = true;
              agentPerms.assign.locked     = true;
            } else if (groupPerms.assign.state) {
              agentPerms.assign.soft_state = true;
              agentPerms.assign.locked     = true;
            }
          }

          // Now set the model state used in the template
          if (agentPerms.full.set_state || agentPerms.full.soft_state) {
            agentPerms.full.state = true;
          }

          if (agentPerms.full.state) {
            agentPerms.assign.soft_state  = true;
            agentPerms.assign.locked      = true;
          }

          if (agentPerms.assign.set_state || agentPerms.assign.soft_state) {
            result.push(agentPerms.assign.state = true);
          } else {
            result.push(undefined);
          }
        }
        return result;
      })();
    }


    /**
    * Takes the value of a permission
      *
      * @param {permission}  perm    The permission to resolve
    */
    setGroupPerm(gid, name, value) {
      const groupPerms = this.groups_map[gid].perms;

      if (value === '&') {
        value = groupPerms[name].state;
      }

      groupPerms[name].set_state = value;

      if (name === 'full') {
        groupPerms.full.state      = value;
        groupPerms.full.soft_state = false;
        groupPerms.full.locked     = false;

        if (value) {
          groupPerms.assign.state = true;
          groupPerms.assign.soft_state = true;
          groupPerms.assign.locked = true;
        } else {
          groupPerms.assign.soft_state = false;
          groupPerms.assign.locked = false;

          if (groupPerms.assign.set_state || groupPerms.assign.soft_state) {
            groupPerms.assign.state = true;
          } else {
            groupPerms.assign.state = false;
          }
        }
      } else {
        if (groupPerms[name].set_state || groupPerms[name].soft_state) {
          groupPerms[name].state = true;
        } else {
          groupPerms[name].state = false;
        }
      }

      return this.refreshAgentGroupPerms();
    }


    /**
    * Takes the value of a permission
      *
      * @param {permission}  perm    The permission to resolve
    */
    setAgentPerm(aid, name, value) {
      const agentPerms = this.agents_map[aid].perms;

      if (value === '&') {
        value = agentPerms[name].state;
      }

      agentPerms[name].set_state = value;

      if (name === 'full') {
        agentPerms.full.state      = value;
        agentPerms.full.soft_state = false;
        agentPerms.full.locked     = false;

        if (value) {
          agentPerms.assign.soft_state = true;
          agentPerms.assign.locked = true;
        } else {
          agentPerms.assign.soft_state = false;
          agentPerms.assign.locked = false;

          if (agentPerms.assign.set_state || agentPerms.assign.soft_state) {
            agentPerms.assign.state = true;
          } else {
            agentPerms.assign.state = false;
          }
        }
      } else {
        if (agentPerms[name].set_state || agentPerms[name].soft_state) {
          agentPerms[name].state = true;
        } else {
          agentPerms[name].state = false;
        }
      }

      return this.refreshAgentGroupPerms(aid);
    }


    /*
      * Get all permission data as an array
      *
      * @return {Array}
    */
    getPermsData() {
      const perms = [];

      for (let agentObj of Array.from(this.agents)) {
        if (agentObj.perms.full.state) {
          perms.push({
            person_id: agentObj.model.id,
            name: 'full',
            value: 1
          });
        } else if (agentObj.perms.assign.state) {
          perms.push({
            person_id: agentObj.model.id,
            name: 'assign',
            value: 1
          });
        }
      }

      for (let groupObj of Array.from(this.groups)) {
        if (groupObj.perms.full.state) {
          perms.push({
            usergroup_id: groupObj.model.id,
            name: 'full',
            value: 1
          });
        } else if (groupObj.perms.assign.state) {
          perms.push({
            usergroup_id: groupObj.model.id,
            name: 'assign',
            value: 1
          });
        }
      }

      return perms;
    }
  });
});