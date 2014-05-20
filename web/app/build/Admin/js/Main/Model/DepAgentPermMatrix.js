(function() {
  define(function() {
    var Admin_Main_Model_DepAgentPermMatrix;
    return Admin_Main_Model_DepAgentPermMatrix = (function() {
      function Admin_Main_Model_DepAgentPermMatrix() {
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

      Admin_Main_Model_DepAgentPermMatrix.prototype.addGroup = function(group, perms_array) {
        var obj, p, perms, _i, _len;
        perms = {};
        for (_i = 0, _len = perms_array.length; _i < _len; _i++) {
          p = perms_array[_i];
          perms[p.name] = {
            state: false,
            set_state: p.state,
            soft_state: false,
            locked: false
          };
        }
        obj = {
          type: 'group',
          model: group,
          perms: perms,
          aids: []
        };
        this.groups.push(obj);
        return this.groups_map[group.id] = obj;
      };


      /**
      		* Add an agnet
        	*
        	* @param {Model}  agent        The agent model
        	* @param {Array}  perms_array  Array of current perm values
       */

      Admin_Main_Model_DepAgentPermMatrix.prototype.addAgent = function(agent, perms_array) {
        var obj, p, perms, _i, _len;
        perms = {};
        for (_i = 0, _len = perms_array.length; _i < _len; _i++) {
          p = perms_array[_i];
          perms[p.name] = {
            state: false,
            set_state: p.state,
            soft_state: true,
            locked: false
          };
        }
        obj = {
          type: 'agent',
          model: agent,
          perms: perms
        };
        this.agents.push(obj);
        return this.agents_map[agent.id] = obj;
      };


      /**
      		* After all perm values are added to the matrix,
        	* this should be called to propogate values from ug's to
        	* agents and set the proper locked state.
       */

      Admin_Main_Model_DepAgentPermMatrix.prototype.initPerms = function(group_perms, agent_perms) {
        var agent, agentObj, agentPerms, aid, gid, group, groupObj, groupPerms, p, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _m, _ref, _ref1, _ref2, _results;
        if (group_perms) {
          for (_i = 0, _len = group_perms.length; _i < _len; _i++) {
            p = group_perms[_i];
            if (this.groups_map[p.usergroup_id] == null) {
              continue;
            }
            if (this.groups_map[p.usergroup_id].perms[p.perm_name] == null) {
              this.groups_map[p.usergroup_id].perms[p.perm_name] = {
                state: false,
                set_state: false,
                soft_state: false,
                locked: false
              };
            }
            this.groups_map[p.usergroup_id].perms[p.perm_name].set_state = true;
          }
        }
        if (agent_perms) {
          for (_j = 0, _len1 = agent_perms.length; _j < _len1; _j++) {
            p = agent_perms[_j];
            if (this.agents_map[p.agent_id] == null) {
              continue;
            }
            if (this.agents_map[p.agent_id].perms[p.perm_name] == null) {
              this.agents_map[p.agent_id].perms[p.perm_name] = {
                state: false,
                set_state: false,
                soft_state: false,
                locked: false
              };
            }
            this.agents_map[p.agent_id].perms[p.perm_name].set_state = true;
          }
        }
        _ref = this.agents;
        for (_k = 0, _len2 = _ref.length; _k < _len2; _k++) {
          agentObj = _ref[_k];
          agent = agentObj.model;
          agentPerms = agentObj.perms;
          if (!agent.agentgroup_ids) {
            continue;
          }
          _ref1 = agent.agentgroup_ids;
          for (_l = 0, _len3 = _ref1.length; _l < _len3; _l++) {
            gid = _ref1[_l];
            if (!this.groups_map[gid]) {
              continue;
            }
            this.groups_map[gid].aids.push(agent.id);
          }
          if (agentPerms.full == null) {
            agentPerms.full = {
              state: false,
              set_state: false,
              soft_state: false,
              locked: false
            };
          }
          if (agentPerms.assign == null) {
            agentPerms.assign = {
              state: false,
              set_state: false,
              soft_state: false,
              locked: false
            };
          }
          if (agentPerms.full.set_state || agentPerms.full.soft_state) {
            agentPerms.full.state = true;
          } else {
            agentPerms.full.state = false;
          }
          if (agentPerms.full.state) {
            agentPerms.assign.soft_state = true;
            agentPerms.assign.locked = true;
          }
          if (agentPerms.assign.set_state || agentPerms.assign.soft_state) {
            agentPerms.assign.state = true;
          } else {
            agentPerms.assign.state = false;
          }
        }
        _ref2 = this.groups;
        _results = [];
        for (_m = 0, _len4 = _ref2.length; _m < _len4; _m++) {
          groupObj = _ref2[_m];
          group = groupObj.model;
          groupPerms = groupObj.perms;
          if (groupPerms.full == null) {
            groupPerms.full = {
              state: false,
              set_state: false,
              soft_state: false,
              locked: false
            };
          }
          if (groupPerms.assign == null) {
            groupPerms.assign = {
              state: false,
              set_state: false,
              soft_state: false,
              locked: false
            };
          }
          if (groupPerms.full.set_state || groupPerms.full.soft_state) {
            groupPerms.full.state = true;
          } else {
            groupPerms.full.state = false;
          }
          if (groupPerms.full.state) {
            groupPerms.assign.soft_state = true;
            groupPerms.assign.locked = true;
          }
          if (groupPerms.assign.set_state || groupPerms.assign.soft_state) {
            groupPerms.assign.state = true;
          } else {
            groupPerms.assign.state = false;
          }
          if (groupPerms.full.state || groupPerms.assign.state) {
            _results.push((function() {
              var _len5, _n, _ref3, _results1;
              _ref3 = groupObj.aids;
              _results1 = [];
              for (_n = 0, _len5 = _ref3.length; _n < _len5; _n++) {
                aid = _ref3[_n];
                if (groupPerms.full.state) {
                  this.agents_map[aid].perms.full.soft_state = true;
                  this.agents_map[aid].perms.full.state = true;
                  this.agents_map[aid].perms.full.locked = true;
                  this.agents_map[aid].perms.assign.soft_state = true;
                  this.agents_map[aid].perms.assign.state = true;
                  _results1.push(this.agents_map[aid].perms.assign.locked = true);
                } else if (groupPerms.assign) {
                  this.agents_map[aid].perms.assign.soft_state = true;
                  this.agents_map[aid].perms.assign.state = true;
                  _results1.push(this.agents_map[aid].perms.assign.locked = true);
                } else {
                  _results1.push(void 0);
                }
              }
              return _results1;
            }).call(this));
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };


      /**
      		* Refreshes permissions on agents based on current group permssions
       */

      Admin_Main_Model_DepAgentPermMatrix.prototype.refreshAgentGroupPerms = function(aid) {
        var agent, agentObj, agentPerms, agents, gid, groupPerms, _i, _j, _len, _len1, _ref, _results;
        if (aid == null) {
          aid = null;
        }
        if (aid) {
          agents = [this.agents_map[aid]];
        } else {
          agents = this.agents;
        }
        _results = [];
        for (_i = 0, _len = agents.length; _i < _len; _i++) {
          agentObj = agents[_i];
          agent = agentObj.model;
          agentPerms = agentObj.perms;
          agentPerms.full.soft_state = false;
          agentPerms.full.locked = false;
          agentPerms.assign.soft_state = false;
          agentPerms.assign.locked = false;
          _ref = agent.agentgroup_ids;
          for (_j = 0, _len1 = _ref.length; _j < _len1; _j++) {
            gid = _ref[_j];
            groupPerms = this.groups_map[gid].perms;
            if (groupPerms.full.state) {
              agentPerms.full.soft_state = true;
              agentPerms.full.locked = true;
              agentPerms.assign.soft_state = true;
              agentPerms.assign.locked = true;
            } else if (groupPerms.assign.state) {
              agentPerms.assign.soft_state = true;
              agentPerms.assign.locked = true;
            }
          }
          if (agentPerms.full.set_state || agentPerms.full.soft_state) {
            agentPerms.full.state = true;
          }
          if (agentPerms.full.state) {
            agentPerms.assign.soft_state = true;
            agentPerms.assign.locked = true;
          }
          if (agentPerms.assign.set_state || agentPerms.assign.soft_state) {
            _results.push(agentPerms.assign.state = true);
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };


      /**
      		* Takes the value of a permission
        	*
        	* @param {permission}  perm    The permission to resolve
       */

      Admin_Main_Model_DepAgentPermMatrix.prototype.setGroupPerm = function(gid, name, value) {
        var groupPerms;
        groupPerms = this.groups_map[gid].perms;
        if (value === '&') {
          value = groupPerms[name].state;
        }
        groupPerms[name].set_state = value;
        if (name === 'full') {
          groupPerms.full.state = value;
          groupPerms.full.soft_state = false;
          groupPerms.full.locked = false;
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
      };


      /**
      		* Takes the value of a permission
        	*
        	* @param {permission}  perm    The permission to resolve
       */

      Admin_Main_Model_DepAgentPermMatrix.prototype.setAgentPerm = function(aid, name, value) {
        var agentPerms;
        agentPerms = this.agents_map[aid].perms;
        if (value === '&') {
          value = agentPerms[name].state;
        }
        agentPerms[name].set_state = value;
        if (name === 'full') {
          agentPerms.full.state = value;
          agentPerms.full.soft_state = false;
          agentPerms.full.locked = false;
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
      };


      /*
        	 * Get all permission data as an array
        	 *
        	 * @return {Array}
       */

      Admin_Main_Model_DepAgentPermMatrix.prototype.getPermsData = function() {
        var agentObj, groupObj, perms, _i, _j, _len, _len1, _ref, _ref1;
        perms = [];
        _ref = this.agents;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          agentObj = _ref[_i];
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
        _ref1 = this.groups;
        for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
          groupObj = _ref1[_j];
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
      };

      return Admin_Main_Model_DepAgentPermMatrix;

    })();
  });

}).call(this);

//# sourceMappingURL=DepAgentPermMatrix.js.map
