(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  define(['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base', 'Admin/Agents/FormModel/EditAgentModel', 'Admin/Agents/FormModel/EditAgentNotifPrefs'], function(Strings, Admin_Ctrl_Base, EditAgentModel, EditAgentNotifPrefs) {
    var Admin_Agents_Ctrl_Edit;
    Admin_Agents_Ctrl_Edit = (function(_super) {
      __extends(Admin_Agents_Ctrl_Edit, _super);

      function Admin_Agents_Ctrl_Edit() {
        this.clearPermOverrides = __bind(this.clearPermOverrides, this);
        return Admin_Agents_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_Agents_Ctrl_Edit.CTRL_ID = 'Admin_Agents_Ctrl_Edit';

      Admin_Agents_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_Agents_Ctrl_Edit.DEPS = [];

      Admin_Agents_Ctrl_Edit.prototype.init = function() {
        this.agentId = parseInt(this.$stateParams.id);
        this.form = {
          email_primary: '',
          emails_list: []
        };
        this.hasPermOverrides = false;
        this.$scope.$watch('EditCtrl.form.emails_list', (function(_this) {
          return function(emails_list) {
            if (!emails_list) {
              return;
            }
            if (!_this.form.email_primary || _this.form.email_primary === '' || emails_list.indexOf(_this.form.email_primary) === -1) {
              if (emails_list.length) {
                return _this.form.email_primary = emails_list[0];
              } else {
                return _this.form.email_primary = '';
              }
            }
          };
        })(this));
      };

      Admin_Agents_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        if (this.agentId) {
          promise = this.Api.sendDataGet({
            agent: "/agents/" + this.agentId + "?extended=1",
            teams: "/agent_teams",
            groups: "/agent_groups",
            groupPerms: "/agent_groups/all/permissions",
            notif_prefs_table: "/agents/" + this.agentId + "/notify-prefs/get-tables",
            ticketDeps: "/ticket_deps?with_perms=1",
            chatDeps: "/chat_deps?with_perms=1"
          });
        } else {
          promise = this.Api.sendDataGet({
            teams: "/agent_teams",
            groups: "/agent_groups",
            groupPerms: "/agent_groups/all/permissions",
            notif_prefs_table: "/agents/0/notify-prefs/get-tables",
            ticketDeps: "/ticket_deps?with_perms=1",
            chatDeps: "/chat_deps?with_perms=1"
          });
        }
        promise.then((function(_this) {
          return function(result) {
            var assign, dep, full, u, _i, _j, _len, _len1, _ref, _ref1, _ref2, _ref3, _results;
            if (_this.agentId) {
              _this.agent = result.data.agent.agent;
              _this.agent.signature_html = result.data.agent.signature_html;
              _this.perm_form = result.data.agent.perms;
            } else {
              _this.agent = {
                id: 0,
                name: '',
                email: {},
                teams: [],
                usergroups: []
              };
              _this.perm_form = null;
            }
            _this.teams = result.data.teams.agent_teams;
            _this.groups = result.data.groups.groups;
            _this.groupPerms = result.data.groupPerms.groups;
            _this.ticketDeps = result.data.ticketDeps.departments;
            _this.chatDeps = result.data.chatDeps.departments;
            _this.agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table);
            _this.notif_prefs = _this.agentNotifPrefsModel.prefsTable;
            _this.agentFormModel = new EditAgentModel(_this.agent, _this.groups, _this.teams);
            _this.form = _this.agentFormModel.form;
            _this.$scope.$watch('EditCtrl.form.agent_groups', function() {
              return _this.updateEffectiveUgPerms();
            }, true);
            _this.updateHasPermOverridesStatus();
            _this.deps_perms = {
              tickets: {},
              chat: {}
            };
            _ref = _this.ticketDeps;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              dep = _ref[_i];
              assign = false;
              full = false;
              if (_this.agentId && ((_ref1 = dep.permissions) != null ? _ref1.users : void 0)) {
                u = dep.permissions.users.filter(function(x) {
                  return x.id === _this.agentId;
                })[0];
                if (u) {
                  if (u.name === 'full') {
                    full = true;
                  } else {
                    assign = true;
                  }
                }
              }
              _this.deps_perms.tickets[dep.id] = {
                assign: assign,
                full: full
              };
            }
            _ref2 = _this.chatDeps;
            _results = [];
            for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
              dep = _ref2[_j];
              full = false;
              if (_this.agentId && ((_ref3 = dep.permissions) != null ? _ref3.users : void 0)) {
                u = dep.permissions.users.filter(function(x) {
                  return x.id === _this.agentId;
                })[0];
                if (u) {
                  full = true;
                }
              }
              _results.push(_this.deps_perms.chat[dep.id] = {
                full: full
              });
            }
            return _results;
          };
        })(this));
        return promise;
      };


      /*
      		 * When usergroups are changed, we need to update the effective list of permissions
       */

      Admin_Agents_Ctrl_Edit.prototype.updateEffectiveUgPerms = function() {
        var assign, dep, full, group, groupIds, info, p, perms, pname, pval, type, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _len5, _m, _n, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6, _results;
        this.ugEffectivePerms = {
          ticket: {},
          people: {},
          org: {},
          chat: {},
          publish: {},
          general: {}
        };
        this.ugEffectiveDepPerms = {
          tickets: {},
          chat: {}
        };
        if (!this.form.agent_groups) {
          return;
        }
        groupIds = [];
        _ref = this.form.agent_groups;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          group = _ref[_i];
          if (group.value) {
            groupIds.push(group.id);
          }
        }
        _ref1 = this.ticketDeps;
        for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
          dep = _ref1[_j];
          assign = false;
          full = false;
          if ((_ref2 = dep.permissions) != null ? _ref2.agentgroups : void 0) {
            perms = dep.permissions.agentgroups.filter(function(x) {
              var _ref3;
              return _ref3 = x.id, __indexOf.call(groupIds, _ref3) >= 0;
            });
            for (_k = 0, _len2 = perms.length; _k < _len2; _k++) {
              p = perms[_k];
              if (p.name === 'full') {
                full = true;
              } else {
                assign = true;
              }
            }
          }
          this.ugEffectiveDepPerms.tickets[dep.id] = {
            assign: assign,
            full: full
          };
        }
        _ref3 = this.chatDeps;
        for (_l = 0, _len3 = _ref3.length; _l < _len3; _l++) {
          dep = _ref3[_l];
          full = false;
          if ((_ref4 = dep.permissions) != null ? _ref4.agentgroups : void 0) {
            perms = dep.permissions.agentgroups.filter(function(x) {
              var _ref5;
              return _ref5 = x.id, __indexOf.call(groupIds, _ref5) >= 0;
            });
            for (_m = 0, _len4 = perms.length; _m < _len4; _m++) {
              p = perms[_m];
              full = true;
            }
          }
          this.ugEffectiveDepPerms.chat[dep.id] = {
            full: full
          };
        }
        _ref5 = this.groupPerms;
        _results = [];
        for (_n = 0, _len5 = _ref5.length; _n < _len5; _n++) {
          info = _ref5[_n];
          if (_ref6 = info.group.id, __indexOf.call(groupIds, _ref6) >= 0) {
            _results.push((function() {
              var _ref7, _results1;
              _ref7 = info.perms;
              _results1 = [];
              for (type in _ref7) {
                if (!__hasProp.call(_ref7, type)) continue;
                perms = _ref7[type];
                _results1.push((function() {
                  var _results2;
                  _results2 = [];
                  for (pname in perms) {
                    if (!__hasProp.call(perms, pname)) continue;
                    pval = perms[pname];
                    if (pval) {
                      _results2.push(this.ugEffectivePerms[type][pname] = pval);
                    } else {
                      _results2.push(void 0);
                    }
                  }
                  return _results2;
                }).call(this));
              }
              return _results1;
            }).call(this));
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };


      /*
        	 * When a permission is updated, we need to update the hasPermOverrides status.
        	 * This is done by an ngChange on the permission toggles. We dont use a watch because
        	 * it can become too slow to watch the large graph of permissions.
       */

      Admin_Agents_Ctrl_Edit.prototype.updateHasPermOverridesStatus = function() {
        var permName, perms, type, value, _ref, _ref1;
        this.updateEffectiveUgPerms();
        this.hasPermOverrides = false;
        _ref = this.perm_form;
        for (type in _ref) {
          if (!__hasProp.call(_ref, type)) continue;
          perms = _ref[type];
          for (permName in perms) {
            if (!__hasProp.call(perms, permName)) continue;
            value = perms[permName];
            if (value) {
              if ((((_ref1 = this.ugEffectivePerms[type]) != null ? _ref1[permName] : void 0) == null) || !this.ugEffectivePerms[type][permName]) {
                this.hasPermOverrides = true;
                return;
              }
            }
          }
        }
      };


      /*
        	 * This does the actual removal of all perm overrides
       */

      Admin_Agents_Ctrl_Edit.prototype.clearPermOverrides = function() {
        var permName, perms, type, value, _ref;
        _ref = this.perm_form;
        for (type in _ref) {
          if (!__hasProp.call(_ref, type)) continue;
          perms = _ref[type];
          for (permName in perms) {
            if (!__hasProp.call(perms, permName)) continue;
            value = perms[permName];
            perms[permName] = false;
          }
        }
        return this.hasPermOverrides = false;
      };


      /*
        	 * Shows the password reset modal
       */

      Admin_Agents_Ctrl_Edit.prototype.showResetPassword = function() {
        var doReset, inst;
        doReset = (function(_this) {
          return function(setPassword) {
            if (!setPassword || !Strings.trim(setPassword)) {
              setPassword = '';
            }
            return _this.Api.sendPostJson("/agents/" + _this.agentId + "/reset-password", {
              set_password: setPassword
            });
          };
        })(this);
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Agents/reset-password-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.password = {
                mode: 'random',
                manual: ''
              };
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.saveResetPassword = function() {
                $scope.is_saving = true;
                if ($scope.password.mode === 'set') {
                  return doReset($scope.password.manual).then((function(_this) {
                    return function() {
                      return $modalInstance.close();
                    };
                  })(this));
                } else {
                  return doReset(false).then((function(_this) {
                    return function() {
                      return $modalInstance.close();
                    };
                  })(this));
                }
              };
            }
          ]
        });
        return inst;
      };


      /*
        	 * Shows the copy settings modal
       */

      Admin_Agents_Ctrl_Edit.prototype.showCopySettings = function() {
        var agents, copySettings, inst, _ref, _ref1;
        agents = (_ref = this.$scope.$parent) != null ? (_ref1 = _ref.ListCtrl) != null ? _ref1.agents : void 0 : void 0;
        if (!agents) {
          return false;
        }
        if (agents.length === 1) {
          this.showAlert('There are no other agents to copy settings from');
          return false;
        }
        agents = agents.filter((function(_this) {
          return function(x) {
            return x.id !== _this.agentId;
          };
        })(this));
        copySettings = (function(_this) {
          return function(settings) {
            var promise;
            promise = _this.Api.sendDataGet({
              agent: "/agents/" + settings.agent_id,
              notif_prefs_table: "/agents/" + settings.agent_id + "/notify-prefs/get-tables",
              teams: "/agent_teams",
              groups: "/agent_groups"
            }).then(function(result) {
              var agent, agentFormModel, agentNotifPrefsModel, c, ckey, form, gids, group, groups, n, notif_prefs, permName, perms, r, rkey, subc, subckey, team, teams, tids, type, val, value, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _len5, _len6, _len7, _len8, _m, _n, _o, _p, _q, _ref10, _ref11, _ref12, _ref13, _ref14, _ref15, _ref16, _ref17, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9, _results;
              agent = result.data.agent.agent;
              teams = result.data.teams.agent_teams;
              groups = result.data.groups.groups;
              agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table);
              notif_prefs = agentNotifPrefsModel.prefsTable;
              agentFormModel = new EditAgentModel(agent, groups, teams);
              form = agentFormModel.form;
              if (settings.zones) {
                _this.form.zones.admin = form.zones.admin;
                _this.form.zones.reports = form.zones.reports;
              }
              if (settings.teams) {
                tids = [];
                _ref2 = form.teams;
                for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
                  team = _ref2[_i];
                  if (team.value) {
                    tids.push(team.id);
                  }
                  tids.push(team.id);
                }
                _ref3 = _this.form.teams;
                for (_j = 0, _len1 = _ref3.length; _j < _len1; _j++) {
                  team = _ref3[_j];
                  team.value = (_ref4 = team.id, __indexOf.call(tids, _ref4) >= 0);
                }
              }
              if (settings.groups) {
                gids = [];
                _ref5 = form.agent_groups;
                for (_k = 0, _len2 = _ref5.length; _k < _len2; _k++) {
                  group = _ref5[_k];
                  if (group.value) {
                    gids.push(group.id);
                  }
                }
                _ref6 = _this.form.agent_groups;
                for (_l = 0, _len3 = _ref6.length; _l < _len3; _l++) {
                  group = _ref6[_l];
                  group.value = (_ref7 = group.id, __indexOf.call(gids, _ref7) >= 0);
                }
              }
              if (settings.perms) {
                _ref8 = agent.perms;
                for (type in _ref8) {
                  if (!__hasProp.call(_ref8, type)) continue;
                  perms = _ref8[type];
                  for (permName in perms) {
                    if (!__hasProp.call(perms, permName)) continue;
                    value = perms[permName];
                    if (((_ref9 = _this.perm_form[type]) != null ? _ref9[permName] : void 0) == null) {
                      continue;
                    }
                    _this.perm_form[type][permName] = value;
                  }
                }
              }
              if (settings.ticket_notifs) {
                _ref10 = ['sys_filters_email', 'sys_filters_alert', 'custom_filters_email', 'custom_filters_alert'];
                for (_m = 0, _len4 = _ref10.length; _m < _len4; _m++) {
                  n = _ref10[_m];
                  if ((_this.notif_prefs.subs[n] != null) && (notif_prefs.subs[n] != null)) {
                    _ref11 = _this.notif_prefs.subs[n].rows;
                    for (rkey = _n = 0, _len5 = _ref11.length; _n < _len5; rkey = ++_n) {
                      r = _ref11[rkey];
                      _ref12 = r.cols;
                      for (ckey = _o = 0, _len6 = _ref12.length; _o < _len6; ckey = ++_o) {
                        c = _ref12[ckey];
                        for (subckey = _p = 0, _len7 = c.length; _p < _len7; subckey = ++_p) {
                          subc = c[subckey];
                          val = ((_ref13 = notif_prefs.subs[n]) != null ? (_ref14 = _ref13.rows[rkey]) != null ? (_ref15 = _ref14.cols[ckey]) != null ? (_ref16 = _ref15[subckey]) != null ? _ref16.value : void 0 : void 0 : void 0 : void 0) || false;
                          _this.notif_prefs.subs[n].rows[rkey].cols[ckey][subckey].value = val;
                        }
                      }
                    }
                  }
                }
              }
              if (settings.other_notifs) {
                _ref17 = ['chat', 'task', 'twitter', 'feedback', 'publish', 'crm', 'account'];
                _results = [];
                for (_q = 0, _len8 = _ref17.length; _q < _len8; _q++) {
                  n = _ref17[_q];
                  if ((_this.notif_prefs.subs[n] != null) && (notif_prefs.subs[n] != null)) {
                    _results.push((function() {
                      var _len9, _r, _ref18, _results1;
                      _ref18 = this.notif_prefs.subs[n].rows;
                      _results1 = [];
                      for (rkey = _r = 0, _len9 = _ref18.length; _r < _len9; rkey = ++_r) {
                        r = _ref18[rkey];
                        _results1.push((function() {
                          var _len10, _ref19, _ref20, _ref21, _ref22, _results2, _s;
                          _ref19 = r.cols;
                          _results2 = [];
                          for (subckey = _s = 0, _len10 = _ref19.length; _s < _len10; subckey = ++_s) {
                            subc = _ref19[subckey];
                            val = ((_ref20 = notif_prefs.subs[n]) != null ? (_ref21 = _ref20.rows[rkey]) != null ? (_ref22 = _ref21.cols[subckey]) != null ? _ref22.value : void 0 : void 0 : void 0) || false;
                            _results2.push(this.notif_prefs.subs[n].rows[rkey].cols[subckey].value = val);
                          }
                          return _results2;
                        }).call(this));
                      }
                      return _results1;
                    }).call(_this));
                  } else {
                    _results.push(void 0);
                  }
                }
                return _results;
              }
            });
            return promise;
          };
        })(this);
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Agents/copy-settings-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              $scope.agents = agents;
              $scope.options = {
                agent_id: agents[0].id + "",
                zones: false,
                teams: false,
                groups: false,
                perms: false,
                ticket_notifs: false,
                other_notifs: false
              };
              return $scope.doCopySettings = function(settings) {
                $scope.is_loading = true;
                return copySettings(settings).then(function() {
                  return $modalInstance.dismiss();
                });
              };
            }
          ]
        });
      };


      /*
        	 * Shows the copy settings modal
       */

      Admin_Agents_Ctrl_Edit.prototype.showLoginAs = function() {
        var Api, agentId, agentName, inst;
        agentName = this.form.name;
        agentId = this.agentId;
        Api = this.Api;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Agents/login-as-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              $scope.agentName = agentName;
              $scope.is_loading = true;
              return Api.sendGet("/agents/" + agentId + "/login-token").then(function(res) {
                $scope.is_loading = false;
                return $scope.login_token = res.data.login_token;
              });
            }
          ]
        });
        return inst.result.then((function(_this) {
          return function() {};
        })(this));
      };


      /*
        	 * Shows the copy settings modal
       */

      Admin_Agents_Ctrl_Edit.prototype.showDelete = function() {
        var deleteAgent, inst;
        deleteAgent = (function(_this) {
          return function(settings) {
            var p, target;
            if (settings.method === 'user') {
              target = "/agents/" + _this.agentId + "/delete/to-user";
            } else {
              target = "/agents/" + _this.agentId + "/delete";
            }
            p = _this.Api.sendDelete(target);
            p.then(function() {
              if (_this.$scope.$parent.ListCtrl != null) {
                _this.$scope.$parent.ListCtrl.removeAgentFromList(_this.agentId);
              }
              return _this.$state.go('agents.agents');
            });
            return p;
          };
        })(this);
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Agents/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              $scope.options = {
                method: 'user'
              };
              return $scope.doDelete = function(options) {
                $scope.is_loading = true;
                return deleteAgent(options).then(function() {
                  return $modalInstance.dismiss();
                });
              };
            }
          ]
        });
      };


      /*
        	 * Shows the copy settings modal
       */

      Admin_Agents_Ctrl_Edit.prototype.showEditProfile = function() {
        return this.$modal.open({
          templateUrl: this.getTemplatePath('Agents/edit-profile-modal.html'),
          controller: 'Admin_Agents_Ctrl_EditProfile',
          resolve: {
            agent: (function(_this) {
              return function() {
                return _this.agent;
              };
            })(this),
            saveMethod: (function(_this) {
              return function() {
                return function(data, from) {
                  if (from.new_image) {
                    _this.agent.picture_blob = from.new_image;
                  } else if (from.form.picture_set === 'default') {
                    _this.agent.picture_blob = null;
                  }
                  _this.agent.timezone = from.form.timeone;
                  _this.agent.signature_html = from.form.signature_html;
                  if (_this.agentId) {
                    return _this.Api.sendPostJson("/agents/" + _this.agentId + "/profile", data);
                  } else {
                    return _this.pendingProfileData = data;
                  }
                };
              };
            })(this)
          }
        });
      };


      /*
        	 * Returns an object hash of the complete form data
       */

      Admin_Agents_Ctrl_Edit.prototype.getFormData = function() {
        var formData;
        formData = {
          agent: this.agentFormModel.getFormData(),
          filter_subs: this.agentNotifPrefsModel.getFilterSubs(),
          other_subs: this.agentNotifPrefsModel.getOtherSubs(),
          perm_overrides: this.perm_form,
          dep_perm_overrides: this.deps_perms
        };
        if (this.pendingProfileData) {
          formData.profile = this.pendingProfileData;
          this.pendingProfileData = null;
        }
        return formData;
      };


      /*
        	 * Saves the agent
       */

      Admin_Agents_Ctrl_Edit.prototype.saveAgent = function() {
        var postData, promise;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        this.email_dupe_error = false;
        this.startSpinner('saving');
        postData = this.getFormData();
        if (this.agentId) {
          promise = this.Api.sendPostJson("/agents/" + this.agentId, postData);
        } else {
          promise = this.Api.sendPutJson("/agents", postData);
        }
        promise.then((function(_this) {
          return function(res) {
            _this.agent.display_name = _this.form.name;
            if (_this.agentId) {
              if (_this.$scope.$parent.ListCtrl != null) {
                _this.$scope.$parent.ListCtrl.updateAgent(_this.agent);
              }
            } else {
              _this.$state.go('agents.agents.edit', {
                id: res.data.person_id
              });
              if (_this.$scope.$parent.ListCtrl != null) {
                _this.$scope.$parent.ListCtrl.addAgent(res.data.person_id, _this.agent.display_name);
              }
            }
            return _this.stopSpinner('saving');
          };
        })(this), (function(_this) {
          return function(res) {
            var _ref;
            if ((res != null ? (_ref = res.data) != null ? _ref.error_code : void 0 : void 0) === 'dupe_email') {
              _this.email_dupe_error = res.data.error_info.existing;
            }
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(res);
          };
        })(this));
        return promise;
      };

      return Admin_Agents_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
