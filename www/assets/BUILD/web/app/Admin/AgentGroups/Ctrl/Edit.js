define(['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) => {
  class Admin_AgentGroups_Ctrl_Edit extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_AgentGroups_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
    }

    init() {
      this.groupId = parseInt(this.$stateParams.id);

      this.service = {
        groups:     this.DataService.get('AgentGroups'),
        agents:     this.DataService.get('Agents'),
        ticketDeps: this.DataService.get('TicketDeps'),
        chatDeps:   this.DataService.get('ChatDeps')
      };

      this.all_perms = {
        perms:      {},
        deps_perms: {
          tickets: { assign: true, full: true },
          chat:    { full: true }
        }
      };

      this.$scope.toggleAgent = (agent) => {
        const index = this.group.person_ids.indexOf(agent.id);
        if (index !== -1) {
          return this.group.person_ids.splice(index, 1);
        }
        return this.group.person_ids.push(agent.id);
      };
    }


    initialLoad() {
      let groupPromise;
      if (this.groupId) {
        groupPromise = this.Api.sendGet(`/agent_groups/${this.groupId}`);
      } else {
        groupPromise = this.service.groups.get(this.groupId);
      }

      const promises = [groupPromise, this.service.agents.all(), this.service.ticketDeps.all(true), this.service.chatDeps.all(true)];

      return this.$q.all(promises).then((res) => {
        let subdep;
        if (res[0] && ((res[0].data != null ? res[0].data.group : undefined) != null)) {
          this.group = res[0].data.group;
        } else {
          this.group = { id: 0 };
        }

        this.agents = res[1];
        // deps need to be flattened to show in the table
        this.chatDeps = [];
        for (var dep of Array.from(res[3])) {
          this.chatDeps.push(dep);
          if (dep.children) {
            for (subdep of Array.from(dep.children)) {
              subdep.depth = 1;
              this.chatDeps.push(subdep);
            }
          }
        }


        // deps need to be flattened to show in the table
        this.ticketDeps = [];
        for (dep of Array.from(res[2])) {
          this.ticketDeps.push(dep);
          if (dep.children) {
            for (subdep of Array.from(dep.children)) {
              subdep.depth = 1;
              this.ticketDeps.push(subdep);
            }
          }
        }

        this.group.person_ids = (this.group.members || []).map(a => a.id);
        this.assignDepsPerms(this.group);
        this.updateAllPermsState();

        if ((this.group.sys_name === 'agent_all_perms') || (this.group.sys_name === 'agent_all_safe_perms')) {
          return this.$scope.all_locked_perms = true;
        }
      });
    }


    // todo load from controller
    assignDepsPerms(group) {
      let full,
        u;
      group.deps_perms = {
        tickets: {},
        chat:    {}
      };

      for (var dep of Array.from(this.ticketDeps)) {
        let assign = false;
        full = false;

        if (dep.permissions != null ? dep.permissions.agentgroups : undefined) {
          u = dep.permissions.agentgroups.filter(x => x.id === group.id)[0];
          if (u) {
            if (u.name === 'full') { full = true; } else { assign = true; }
          }
        }

        group.deps_perms.tickets[dep.id] = { assign, full };
      }

      return (() => {
        const result = [];
        for (dep of Array.from(this.chatDeps)) {
          full = false;
          if (dep.permissions != null ? dep.permissions.agentgroups : undefined) {
            u = dep.permissions.agentgroups.filter(x => x.id === group.id)[0];
            if (u) {
              full = true;
            }
          }

          result.push(group.deps_perms.chat[dep.id] = { full });
        }
        return result;
      })();
    }


    changeAllPerms(type, section) {
      if ((this.group == null)) { return; }

      if ((type === 'perms') && this.group.perms && this.group.perms[section]) {
        for (const perm of Object.keys(this.group.perms[section] || {})) {
          this.group.perms[section][perm] = this.all_perms[type][section];
        }

        if (section === 'people') {
          return this.changeAllPerms('perms', 'org');
        }
      } else if ((type === 'deps_perms_tickets') && this.group.deps_perms.tickets) {
        return (() => {
          const result = [];
          for (const dep of Object.keys(this.group.deps_perms.tickets || {})) {
            result.push(this.group.deps_perms.tickets[dep][section] = this.all_perms.deps_perms.tickets[section]);
          }
          return result;
        })();
      } else if ((type === 'deps_perms_chat') && this.group.deps_perms.chat) {
        return (() => {
          const result1 = [];
          for (const dep of Object.keys(this.group.deps_perms.chat || {})) {
            result1.push(this.group.deps_perms.chat[dep][section] = this.all_perms.deps_perms.chat[section]);
          }
          return result1;
        })();
      }
    }


    updateAllPermsState() {
      let enabled;
      if ((this.group == null)) { return; }

      for (var section of Object.keys(this.group.perms || {})) {
        const perms = this.group.perms[section];
        enabled = true;
        for (const perm of Object.keys(perms || {})) {
          if (!perms[perm]) {
            enabled = false;
            break;
          }
        }
        this.all_perms.perms[section] = enabled;
      }

      if (!this.group.deps_perms) { return; }
      return (() => {
        const result = [];
        for (var type of Object.keys(this.all_perms.deps_perms || {})) {
          var sections = this.all_perms.deps_perms[type];
          result.push((() => {
            const result1 = [];
            for (section of Object.keys(sections || {})) {
              enabled = true;
              for (const dep of Object.keys(this.group.deps_perms[type] || {})) {
                if (!this.group.deps_perms[type][dep][section]) {
                  enabled = false;
                }
              }
              result1.push(this.all_perms.deps_perms[type][section] = enabled);
            }
            return result1;
          })());
        }
        return result;
      })();
    }


    saveForm() {
      let p;
      const postData = {
        group:     this.group,
        dep_perms: this.group.deps_perms
      };

      if (this.groupId) {
        p = this.sendFormSaveApiCall('POST', `/agent_groups/${this.groupId}`, postData);
      } else {
        p = this.sendFormSaveApiCall('PUT', '/agent_groups', postData);
      }

      p.then((res) => {
        this.Growl.success(this.getRegisteredMessage('saved_group'));

        if (!this.groupId) {
          this.groupId = (this.group.id = res.data.group_id);
          this.service.groups._addModel(this.group);
        }

        // force reload deps perms
        // todo move deps perms as @group attribute
        this.$q.all([this.service.ticketDeps.all(true), this.service.chatDeps.all(true)]);

        return this.$state.go('agents.groups.edit', { id: this.groupId });
      });
    }


    showDelete() {
      let inst;
      const deleteGroup = () => this.service.groups.remove(this.group).then(() => {
          // force reload deps perms
          // todo move deps perms as @group attribute
        this.$q.all([this.service.ticketDeps.all(true), this.service.chatDeps.all(true)]);
        return this.$state.go('agents.groups');
      });

      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('AgentGroups/delete-modal.html'),
        controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.doDelete = function (options) {
            $scope.is_loading = true;
            return deleteGroup().then(() => $modalInstance.dismiss());
          };
        }
        ]
      });
    }


    /*
     * Shows the copy settings modal
     */
    showCopySettings() {
      const groups = [];

      //------------------------------
      // Function callback that loads and applies the settings
      //------------------------------

      const copySettings = settings => this.service.groups.get(settings.group_id).then((group) => {
        if ((group == null)) { return; }

        if (settings.copy_perms) {
          this.group.perms = {};
          angular.copy(group.perms, this.group.perms);
        }

        if (settings.copy_deps_perms) {
          this.assignDepsPerms(group);
          this.group.deps_perms = {};
          return angular.copy(group.deps_perms, this.group.deps_perms);
        }
      });

      //------------------------------
      // Show the modal
      //------------------------------

      return this.service.groups.all().then((list) => {
        let inst;
        list.map((group) => { if (group !== this.group) { return groups.push(group); } });

        if (!groups.length) {
          return this.showAlert('There are no other groups to copy permissions from');
        }

        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('AgentGroups/copy-perms-modal.html'),
          controller:  ['$scope', '$modalInstance', function ($scope, $modalInstance) {
            $scope.groups = groups;
            $scope.options = {
              group_id:        groups[0].id,
              copy_perms:      true,
              copy_deps_perms: true
            };

            $scope.dismiss = () => $modalInstance.dismiss();

            return $scope.doCopySettings = function () {
              $scope.is_loading = true;
              return copySettings($scope.options).then(() => $modalInstance.dismiss());
            };
          }
          ]
        });
      });
    }
  }
  Admin_AgentGroups_Ctrl_Edit.initClass();


  return Admin_AgentGroups_Ctrl_Edit.EXPORT_CTRL();
});
