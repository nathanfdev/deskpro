/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS103: Rewrite code to no longer use __guard__
 * DS203: Remove `|| {}` from converted for-own loops
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Strings',
  'Admin/Main/Ctrl/Base',
  'Admin/Agents/FormModel/EditAgentModel',
  'Admin/Agents/FormModel/EditAgentNotifPrefs'
], function(
  Strings,
  Admin_Ctrl_Base,
  EditAgentModel,
  EditAgentNotifPrefs
) {
  class Admin_Agents_Ctrl_Edit extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.clearPermOverrides = this.clearPermOverrides.bind(this);
      this.clearDepOverrides = this.clearDepOverrides.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID   = 'Admin_Agents_Ctrl_Edit';
      this.CTRL_AS   = 'EditCtrl';
      this.DEPS      = ['DpLicense'];
    }

    init() {
      window.AGENT_CTRL = this;
      this.agentId = parseInt(this.$stateParams.id);
      this.created_agent = this.$stateParams.created_agent;
      this.form = {email_primary: '', emails_list: []};
      this.hasPermOverrides = false;
      this.hasDepOverrides = false;
      this.default_phone_number_region = 'US';
      this.service =
        {agents: this.DataService.get('Agents')};
      this.all_perms = {
        perms: {},
        deps_perms: {
          tickets: {assign: true, full: true},
          chat: {full: true}
        }
      };

      this.$scope.$watch('EditCtrl.form.emails_list', emails_list => {
        this.email_sysaccount_error = false;
        if (!emails_list) { return; }
        if (!this.form.email_primary || (this.form.email_primary === '') || (emails_list.indexOf(this.form.email_primary) === -1)) {
          if (emails_list.length) {
            return this.form.email_primary = emails_list[0];
          } else {
            return this.form.email_primary = '';
          }
        }
      });

      this.$scope.$watch('EditCtrl.form.zones.admin', () => {
        if (((this.form != null ? this.form.zones : undefined) == null)) { return; }
        return this.form.zones.reports = this.form.zones.reports || this.form.zones.admin;
      });
      this.$scope.$watch('EditCtrl.form.zones.reports', () => {
        if (((this.form != null ? this.form.zones : undefined) == null)) { return; }
        return this.form.zones.reports = this.form.zones.reports || this.form.zones.admin;
      });

      this.$scope.show_selected_permissions = false;
      this.$scope.show_selected_teams = false;
      this.$scope.selectedFilter = show_selected =>
        itm => !show_selected || itm.value
      ;

    }

    initialLoad() {
      let promise;
      if (this.agentId) {
        promise = this.Api.sendDataGet({
          agent: `/agents/${this.agentId}?extended=1`,
          teams: "/agent_teams",
          groups: "/agent_groups",
          groupPerms: "/agent_groups/all/permissions",
          notif_prefs_table: `/agents/${this.agentId}/notify-prefs/get-tables`,
          ticketDeps: "/ticket_deps?with_perms=1",
          chatDeps: "/chat_deps?with_perms=1",
          default_country: "/settings/values/core.default_country_code"
        });
      } else {
        promise = this.Api.sendDataGet({
          teams: "/agent_teams",
          groups: "/agent_groups",
          groupPerms: "/agent_groups/all/permissions",
          notif_prefs_table: "/agents/0/notify-prefs/get-tables",
          ticketDeps: "/ticket_deps?with_perms=1",
          chatDeps: "/chat_deps?with_perms=1",
          default_country: "/settings/values/core.default_country_code"
        });
      }

      promise.then( result => {
        if (this.agentId) {
          this.agent = result.data.agent.agent;
          this.perm_form = result.data.agent.perm_overrides;
        } else {
          this.agent = {
            id: 0,
            name: '',
            email: {},
            teams: [],
            usergroups: []
          };
          this.perm_form = null;
        }

        if (result.data.default_country.value) {
          this.default_phone_number_region = result.data.default_country.value;
        }
        this.primary_phone_number_region = result.data.default_country.value || this.default_phone_number_region;

        this.teams  = result.data.teams.agent_teams;
        this.groups = result.data.groups.groups;
        this.groupPerms = result.data.groupPerms.groups;

        this.ticketDeps = result.data.ticketDeps.departments;
        this.chatDeps   = result.data.chatDeps.departments;

        this.agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table);
        this.notif_prefs = this.agentNotifPrefsModel.prefsTable;

        this.agentFormModel = new EditAgentModel(this.agent, this.groups, this.teams, this.primary_phone_number_region);
        this.form = this.agentFormModel.form;

        if (this.form.primary_phone) {
          this.primary_phone_number_region = this.form.primary_phone.region;
        }

        this.$scope.$watch('EditCtrl.form.agent_groups', () => {
          this.updateEffectiveUgPerms();
          return this.updateAllPermsState();
        }
        , true);

        //--------------------
        // Departments
        //--------------------

        this.deps_perms = this.parseDepPermOverrides(this.agentId, this.ticketDeps, this.chatDeps);
        return this.$timeout(() => this.updateHasPermOverridesStatus());
      });
      return promise;
    }

    hasAnyDepTicketsPerms() {
      for (let perm of Object.keys(this.deps_perms['tickets'] || {})) {
        const obj = this.deps_perms['tickets'][perm];
        if (obj.assign) {
          return true;
        }
      }
      return false;
    }

    canCreateNewTicket() {
      return this.perm_form && (this.perm_form['ticket'] != null) && this.perm_form['ticket'].create;
    }

    changeUse(type) {
      if ((this.perm_form[type] == null) || (true === this.perm_form[type].use)) { return; }
      return (() => {
        const result = [];
        for (let perm of Object.keys(this.perm_form[type] || {})) {
          result.push(this.perm_form[type][perm] = false);
        }
        return result;
      })();
    }



    changeAllPerms(type, section) {
      let dep;
      if ((this.perm_form == null) || (this.deps_perms == null)) { return; }

      if ('perms' === type) {
        for (let perm of Object.keys(this.perm_form[section] || {})) {
          if (((this.ugEffectivePerms[section] != null ? this.ugEffectivePerms[section][perm] : undefined) == null) || !this.ugEffectivePerms[section][perm]) {
            this.perm_form[section][perm] = this.all_perms[type][section];
          }
        }

        if ('people' === section) {
          this.changeAllPerms('perms', 'org');
        }

      } else if ('deps_perms_tickets' === type) {
        for (dep of Object.keys(this.deps_perms.tickets || {})) {
          if (((this.ugEffectiveDepPerms.tickets[dep] != null ? this.ugEffectiveDepPerms.tickets[dep][section] : undefined) == null) || !this.ugEffectiveDepPerms.tickets[dep][section]) {
            this.deps_perms.tickets[dep][section] = this.all_perms.deps_perms.tickets[section];
          }
        }

      } else if ('deps_perms_chat' === type) {
        for (dep of Object.keys(this.deps_perms.chat || {})) {
          if (((this.ugEffectiveDepPerms.chat[dep] != null ? this.ugEffectiveDepPerms.chat[dep][section] : undefined) == null) || !this.ugEffectiveDepPerms.chat[dep][section]) {
            this.deps_perms.chat[dep][section] = this.all_perms.deps_perms.chat[section];
          }
        }
      }

      return this.updateHasPermOverridesStatus();
    }



    updateAllPermsState() {
      let enabled, perm, perms;
      if ((this.perm_form == null)) { return; }

      // check "use" state first
      for (var section of Object.keys(this.perm_form || {})) {
        perms = this.perm_form[section];
        for (perm of Object.keys(perms || {})) {
          if (('use' !== perm) && (perms.use != null) && (perms[perm] || (this.ugEffectivePerms[section] != null ? this.ugEffectivePerms[section][perm] : undefined))) {
            perms.use = true;
            break;
          }
        }
      }

      // and this one is for "toggle all"
      for (section of Object.keys(this.perm_form || {})) {
        perms = this.perm_form[section];
        enabled = true;
        for (perm of Object.keys(perms || {})) {
          if (!perms[perm] && !(this.ugEffectivePerms[section] != null ? this.ugEffectivePerms[section][perm] : undefined)) {
            enabled = false;
            break;
          }
        }
        this.all_perms.perms[section] = enabled;
      }

      return (() => {
        const result = [];
        for (var type of Object.keys(this.all_perms.deps_perms || {})) {
          var sections = this.all_perms.deps_perms[type];
          result.push((() => {
            const result1 = [];
            for (section of Object.keys(sections || {})) {
              enabled = true;
              for (let dep of Object.keys(this.deps_perms[type] || {})) {
                if (!this.deps_perms[type][dep][section] && !this.ugEffectiveDepPerms[type][dep][section]) {
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



    /*
     * When usergroups are changed, we need to update the effective list of permissions
     */
    updateEffectiveUgPerms() {

      // todo this map should be loaded from server
      let full, p, perms;
      this.ugEffectivePerms = {
        ticket: {},
        people: {},
        org: {},
        chat: {},
        publish: {},
        general: {},
        tasks: {},
        problems: {},
        snippet: {}
      };

      this.ugEffectiveDepPerms = {
        tickets: {},
        chat: {}
      };

      if (!this.form.agent_groups) { return; }

      const groupIds = [];
      for (let group of Array.from(this.form.agent_groups)) {
        if (group.value) {
          groupIds.push(group.id);
        }
      }

      for (var dep of Array.from(this.ticketDeps)) {
        let assign = false;
        full = false;

        if (dep.permissions != null ? dep.permissions.agentgroups : undefined) {
          perms = dep.permissions.agentgroups.filter(x => Array.from(groupIds).includes(x.id));
          for (p of Array.from(perms)) {
            if (p.name === 'full') { full = true; } else { assign = true; }
          }
        }

        this.ugEffectiveDepPerms.tickets[dep.id] = { assign, full };
      }

      for (dep of Array.from(this.chatDeps)) {
        full = false;

        if (dep.permissions != null ? dep.permissions.agentgroups : undefined) {
          perms = dep.permissions.agentgroups.filter(x => Array.from(groupIds).includes(x.id));
          for (p of Array.from(perms)) {
            full = true;
          }
        }

        this.ugEffectiveDepPerms.chat[dep.id] = { full };
      }

      return (() => {
        const result = [];
        for (var info of Array.from(this.groupPerms)) {
          if (Array.from(groupIds).includes(info.group.id)) {
            result.push((() => {
              const result1 = [];
              for (var type of Object.keys(info.perms || {})) {
                perms = info.perms[type];
                result1.push((() => {
                  const result2 = [];
                  for (let pname of Object.keys(perms || {})) {
                    const pval = perms[pname];
                    if (pval) {
                      result2.push(this.ugEffectivePerms[type][pname] = pval);
                    } else {
                      result2.push(undefined);
                    }
                  }
                  return result2;
                })());
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

    hasSomePerms(typename, permname) {
      let name, val;
      const prefix = permname.replace(/(^.*?_).*?$/, '$1');
      const suffix = permname.replace(/^.*?(_.*?)$/, '$1');
      if (!suffix || !(((this.ugEffectivePerms != null ? this.ugEffectivePerms[typename] : undefined) != null) || ((this.perm_form != null ? this.perm_form[typename] : undefined) != null))) { return; }

      if ((this.ugEffectivePerms != null ? this.ugEffectivePerms[typename] : undefined) != null) {
        for (name of Object.keys(this.ugEffectivePerms[typename] || {})) {
          val = this.ugEffectivePerms[typename][name];
          if (val && ((name.indexOf(suffix) !== -1) && (name.indexOf(prefix) === 0))) {
            return true;
          }
        }
      }

      if ((this.perm_form != null ? this.perm_form[typename] : undefined) != null) {
        for (name of Object.keys(this.perm_form[typename] || {})) {
          val = this.perm_form[typename][name];
          if (val && ((name.indexOf(suffix) !== -1) && (name.indexOf(prefix) === 0))) {
            return true;
          }
        }
      }

      return false;
    }

    /*
      * When a permission is updated, we need to update the hasPermOverrides status.
      * This is done by an ngChange on the permission toggles. We dont use a watch because
      * it can become too slow to watch the large graph of permissions.
    */
    updateHasPermOverridesStatus() {
      if ((this.ugEffectivePerms == null) || (this.ugEffectiveDepPerms == null)) { return; }

      this.hasPermOverrides = false;
      let run = () => {
        for (let type of Object.keys(this.perm_form || {})) {
          const perms = this.perm_form[type];
          for (let permName of Object.keys(perms || {})) {
            const value = perms[permName];
            if (value) {
              if (((this.ugEffectivePerms[type] != null ? this.ugEffectivePerms[type][permName] : undefined) == null) || !this.ugEffectivePerms[type][permName] || !this.form.agent_groups.length) {
                this.hasPermOverrides = true;
                return;
              }
            }
          }
        }
      };
      run();

      this.hasDepOverrides = false;
      run = () => {
        if (!this.deps_perms || !this.deps_perms.tickets) { return; }
        for (let app of ['tickets', 'chat']) {
          for (let depId of Object.keys(this.deps_perms[app] || {})) {
            const perms = this.deps_perms[app][depId];
            for (let perm of Object.keys(perms || {})) {
              const value = perms[perm];
              if (value) {
                if (!this.ugEffectiveDepPerms[app][depId][perm] || !this.form.agent_groups.length) {
                  this.hasDepOverrides = true;
                  return;
                }
              }
            }
          }
        }
      };
      run();

      return this.updateAllPermsState();
    }


    /*
      * This does the actual removal of all perm overrides
    */
    clearPermOverrides() {
      for (let type of Object.keys(this.perm_form || {})) {
        const perms = this.perm_form[type];
        for (let permName of Object.keys(perms || {})) {
          const value = perms[permName];
          perms[permName] = false;
        }
      }
      return this.hasPermOverrides = false;
    }

    /*
      * This does the actual removal of all depoverrides
    */
    clearDepOverrides() {
      for (let app of ['tickets', 'chat']) {
        for (let depId of Object.keys(this.deps_perms[app] || {})) {
          const perms = this.deps_perms[app][depId];
          for (let perm of Object.keys(perms || {})) {
            const value = perms[perm];
            this.deps_perms[app][depId][perm] = false;
          }
        }
      }
      return this.hasDepOverrides = false;
    }


    /*
      * Shows the password reset modal
      */
    showResetPassword() {
      const doReset = setPassword => {
        if (!setPassword || !Strings.trim(setPassword)) {
          setPassword = '';
        }

        return this.Api.sendPostJson(`/agents/${this.agentId}/reset-password`, {
          set_password: setPassword
        });
      };

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath(`Agents/reset-password-modal.html?${(new Date()).getTime()}` ),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.password = {
            mode: 'random',
            manual: ''
          };

          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.saveResetPassword = function() {
            $scope.is_saving = true;
            $scope.error = null;

            if ($scope.password.mode === 'set') {
              return doReset($scope.password.manual).then(
                () => $modalInstance.close(),
                res => {
                  $scope.is_saving = false;
                  $scope.error = res.data.error_message;
                  return $scope.error_code = res.data.error_info.error_code;
              });
            } else {
              return doReset(false).then(
                () => $modalInstance.close(),
                res => {
                  $scope.is_saving = false;
                  return $scope.error = res.data.error_message;
              });
            }
          };
        }
        ]
      });

      return inst;
    }

    /*
      * Shows the copy settings modal
      */
    showCopySettings() {

      //------------------------------
      // Get agent options
      //------------------------------

      // The list pane is open right now and has the list of agents we can use
      let inst;
      let agents = __guard__(this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined, x => x.agents);
      if (!agents) { return false; }

      if (agents.length === 1) {
        this.showAlert('There are no other agents to copy settings from');
        return false;
      }

      // Dont include ourself in the list
      agents = agents.filter(x => x.id !== this.agentId);

      //------------------------------
      // Function callback that loads and applies the settings
      //------------------------------

      const copySettings = settings => {
        const promise = this.Api.sendDataGet({
          agent: `/agents/${settings.agent_id}?extended=1`,
          notif_prefs_table: `/agents/${settings.agent_id}/notify-prefs/get-tables`,
          teams: "/agent_teams",
          groups: "/agent_groups"
        }).then( result => {
          let n, r, rkey, subc, subckey, val;
          const { agent }  = result.data.agent;
          const teams  = result.data.teams.agent_teams;
          const { groups } = result.data.groups;

          const agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table);
          const notif_prefs = agentNotifPrefsModel.prefsTable;

          const agentFormModel = new EditAgentModel(agent, groups, teams);
          const { form } = agentFormModel;

          if (settings.zones) {
            this.form.zones.admin   = form.zones.admin;
            this.form.zones.reports = form.zones.reports || form.zones.admin;
          }

          if (settings.teams) {
            const tids = {};
            for (var team of Array.from(form.teams)) {
              tids[team.id] = team.value;
            }
            for (team of Array.from(this.form.teams)) {
              team.value = tids[team.id];
            }
          }

          if (settings.groups) {
            const gids = [];
            for (var group of Array.from(form.agent_groups)) {
              if (group.value) { gids.push(group.id); }
            }
            for (group of Array.from(this.form.agent_groups)) {
              group.value = Array.from(gids).includes(group.id);
            }
          }

          if (settings.perms) {
            this.perm_form = angular.copy(result.data.agent.perm_overrides);
            this.deps_perms = this.parseDepPermOverrides(agent.id, this.ticketDeps, this.chatDeps);
          }

          if (settings.ticket_notifs) {
            for (n of ['sys_filters_email', 'sys_filters_alert', 'custom_filters_email', 'custom_filters_alert']) {
              if ((this.notif_prefs.subs[n] != null) && (notif_prefs.subs[n] != null)) {
                for (rkey = 0; rkey < this.notif_prefs.subs[n].rows.length; rkey++) {
                  r = this.notif_prefs.subs[n].rows[rkey];
                  for (var ckey = 0; ckey < r.cols.length; ckey++) {
                    const c = r.cols[ckey];
                    for (subckey = 0; subckey < c.length; subckey++) {
                      subc = c[subckey];
                      val = __guard__(__guard__(__guard__(notif_prefs.subs[n] != null ? notif_prefs.subs[n].rows[rkey] : undefined, x3 => x3.cols[ckey]), x2 => x2[subckey]), x1 => x1.value) || false;
                      this.notif_prefs.subs[n].rows[rkey].cols[ckey][subckey].value = val;
                    }
                  }
                }
              }
            }
          }

          if (settings.other_notifs) {
            return (() => {
              const result1 = [];
              for (n of ['chat', 'task', 'twitter', 'feedback', 'publish', 'crm', 'account']) {
                if ((this.notif_prefs.subs[n] != null) && (notif_prefs.subs[n] != null)) {
                  result1.push((() => {
                    const result2 = [];
                    for (rkey = 0; rkey < this.notif_prefs.subs[n].rows.length; rkey++) {
                      r = this.notif_prefs.subs[n].rows[rkey];
                      result2.push((() => {
                        const result3 = [];
                        for (subckey = 0; subckey < r.cols.length; subckey++) {
                          subc = r.cols[subckey];
                          val = __guard__(__guard__(notif_prefs.subs[n] != null ? notif_prefs.subs[n].rows[rkey] : undefined, x5 => x5.cols[subckey]), x4 => x4.value) || false;
                          result3.push(this.notif_prefs.subs[n].rows[rkey].cols[subckey].value = val);
                        }
                        return result3;
                      })());
                    }
                    return result2;
                  })());
                } else {
                  result1.push(undefined);
                }
              }
              return result1;
            })();
          }
        });
        this.$timeout(() => this.updateHasPermOverridesStatus());
        return promise;
      };

      //------------------------------
      // Show the modal
      //------------------------------

      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Agents/copy-settings-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          $scope.agents = agents;
          $scope.options = {
            agent_id: agents[0].id+"",
            zones: false,
            teams: false,
            groups: false,
            perms: false,
            ticket_notifs: false,
            other_notifs: false
          };

          return $scope.doCopySettings = function(settings) {
            $scope.is_loading = true;
            return copySettings(settings).then(() => $modalInstance.dismiss());
          };
        }
        ]
      });
    }


    /*
      * Shows the copy settings modal
      */
    showLoginAs() {
      const agentName = this.form.name;
      const { agentId } = this;
      const { Api } = this;

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Agents/login-as-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          $scope.agentName = agentName;
          $scope.is_loading = true;

          return Api.sendGet(`/agents/${agentId}/login-token`).then( function(res) {
            $scope.is_loading = false;
            return $scope.login_token = res.data.login_token;
          });
        }
        ]
      });

      return inst.result.then(() => {

      });
    }


    /*
      * Shows the copy settings modal
      */
    showDelete() {
      let inst;
      const isSelf = this.isSelf();

      const deleteAgent = settings => {
        let target;
        if (settings.method === 'user') {
          target = `/agents/${this.agentId}/delete/to-user`;
        } else {
          target = `/agents/${this.agentId}/delete`;
        }

        const p = this.Api.sendDelete(target);
        p.then(() => {
          // todo
          this.service.agents.get(this.agentId).then(agent => {
            return this.service.agents._removeModel(agent);
          });
          if (this.$scope.$parent != null) {
            this.$scope.$parent.ListCtrl.deletedCount++;
          }
          return this.$state.go('agents.agents');
        });

        return p;
      };

      return inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Agents/delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          $scope.options = {
            method: 'user'
          };

          $scope.isSelf = isSelf;

          return $scope.doDelete = function(options) {
            $scope.is_loading = true;
            return deleteAgent(options).then(() => $modalInstance.dismiss());
          };
        }
        ]
      });
    }

    /*
      * Shows the copy settings modal
      */
    showEditProfile() {
      return this.$modal.open({
        templateUrl: this.getTemplatePath('Agents/edit-profile-modal.html'),
        controller: 'Admin_Agents_Ctrl_EditProfile',
        resolve: {
          agent: () => {
            return this.agent;
          },
          saveMethod: () => {
            return (data, from) => {
              if (from.new_image) {
                this.agent.picture_blob = from.new_image;
              } else if (from.form.picture_set === 'default') {
                this.agent.picture_blob = null;
              }

              this.agent.timezone = from.form.timeone;
              this.agent.signature_html = from.form.signature_html;

              if (this.agentId) {
                return this.Api.sendPostJson(`/agents/${this.agentId}/profile`, data);
              } else {
                return this.pendingProfileData = data;
              }
            };
          }
        }
      });
    }

    mergeDupePerson(personId) {
      const merge = new window.parent.DeskPRO.Agent.Widget.Merge({
        tabType: 'person',
        metaId: this.agentId,
        metaIdName: 'person_id',
        overlayUrl: DP_BASE_URL + 'agent/people/{id}/merge-overlay/{other}',
        mergeUrl: DP_BASE_URL + 'agent/people/{id}/merge/{other}',
        loadRoute: `person:${DP_BASE_URL}agent/people/{id}`
      });

      return merge.openWithId(personId);
    }

    /*
      * Returns an object hash of the complete form data
    */
    getFormData() {
      const formData = {
        agent:              this.agentFormModel.getFormData(),
        filter_subs:        this.agentNotifPrefsModel.getFilterSubs(),
        other_subs:         this.agentNotifPrefsModel.getOtherSubs(),
        perm_overrides:     this.perm_form,
        dep_perm_overrides: this.deps_perms
      };

      if (this.pendingProfileData) {
        formData.profile = this.pendingProfileData;
        this.pendingProfileData = null;
      }

      return formData;
    }

    saveAgent() {
      if (!this.$scope.form_props.$valid) {
        return;
      }

      if (this.agentId) {
        return this.doSaveAgent();
      } else {
        const d = this.$q.defer();

        this.startSpinner('saving');

        this.DpLicense.getLicInfo(true).then( licInfo => {
          this.stopSpinner('saving', true);
          if (licInfo.limits.remain_agents !== 0) {
            return this.doSaveAgent().then(() => d.resolve()
            , () => d.reject());
          } else {
            return this.DpLicense.openUpgradeLicense('upgrade_plan').then(() => {
              return this.doSaveAgent().then(() => d.resolve()
              , () => d.reject());
            });
          }
        }
        , () => {
          this.stopSpinner('saving', true);
          return d.reject();
        });

        return d.promise;
      }
    }

    /*
      * Saves the agent
    */
    doSaveAgent() {
      let promise;
      if (!this.$scope.form_props.$valid) {
        return;
      }

      this.email_dupe_error = false;
      this.email_sysaccount_error = false;
      this.invalid_phone_error = false;
      this.startSpinner('saving');

      let postData = this.getFormData();

      if (window.AGENT_SAVE_FORM_DATA_FILTER) {
        postData = window.AGENT_SAVE_FORM_DATA_FILTER(postData, this);
      }

      if (this.agentId) {
        promise = this.Api.sendPostJson(`/agents/${this.agentId}`, postData);
      } else {
        promise = this.Api.sendPutJson("/agents", postData);
      }

      promise.then( res => {
        this.agent.display_name = this.form.name;
        this.service.agents.mergeDataModel(this.agent);

        if (!this.agentId) {
          this.service.agents.all(true);
          this.$state.go('agents.agents.edit', {id: res.data.person_id, created_agent: 1});
        }

        return this.stopSpinner('saving');
      }
      , res => {
        if (__guard__(res != null ? res.data : undefined, x => x.error_code) === 'dupe_email') {
          this.email_dupe_error = res.data.error_info.existing;
        }
        if (__guard__(res != null ? res.data : undefined, x1 => x1.error_code) === 'system_email_addresses') {
          this.email_sysaccount_error = res.data.error_info.emails.join(', ');
        }
        if (__guard__(res != null ? res.data : undefined, x2 => x2.error_code) === 'invalid_phone_number') {
          this.invalid_phone_error = res.data.error_message + ': ' + (res.data.error_info != null ? res.data.error_info.primary_phone : undefined);
        }
        if (__guard__(__guard__(res != null ? res.data : undefined, x4 => x4.errors), x3 => x3.errors)) {
          res.data.errors.errors.map(error => {
            if ('agent.primary_phone.number' === error.prop) {
              return this.invalid_phone_error = error.message;
            }
          });
        }
        if (__guard__(res != null ? res.data : undefined, x5 => x5.error_code) === 'license_exceeded') {
          this.DpLicense.openUpgradeLicense('upgrade_plan').then(() => {
            return this.doSaveAgent();
          });
        }

        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(res);
      });

      return promise;
    }



    isSelf() {
      return window.DP_PERSON_ID === this.agentId;
    }



    parseDepPermOverrides(agentId, ticketDeps, chatDeps) {
      let full, u;
      const overrides = {
        tickets: {},
        chat: {}
      };

      for (var dep of Array.from(ticketDeps)) {
        let assign = false;
        full = false;

        if (agentId && (dep.permissions != null ? dep.permissions.users : undefined)) {
          u = dep.permissions.users.filter(x => x.id === agentId)[0];
          if (u) {
            if (u.name === 'full') { full = true; } else { assign = true; }
          }
        }

        overrides.tickets[dep.id] = { assign, full };
      }

      for (dep of Array.from(chatDeps)) {
        full = false;
        if (agentId && (dep.permissions != null ? dep.permissions.users : undefined)) {
          u = dep.permissions.users.filter(x => x.id === agentId)[0];
          if (u) {
            full = true;
          }
        }

        overrides.chat[dep.id] = { full };
      }

      return overrides;
    }
  }
  Admin_Agents_Ctrl_Edit.initClass();


  return Admin_Agents_Ctrl_Edit.EXPORT_CTRL();
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}