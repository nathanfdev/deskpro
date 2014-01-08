(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  define(['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base', 'Admin/Agents/FormModel/EditAgentModel', 'Admin/Agents/FormModel/EditAgentNotifPrefs'], function(Strings, Admin_Ctrl_Base, EditAgentModel, EditAgentNotifPrefs) {
    var Admin_Agents_Ctrl_Edit, _ref;
    Admin_Agents_Ctrl_Edit = (function(_super) {
      __extends(Admin_Agents_Ctrl_Edit, _super);

      function Admin_Agents_Ctrl_Edit() {
        this.clearPermOverrides = __bind(this.clearPermOverrides, this);
        _ref = Admin_Agents_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Agents_Ctrl_Edit.CTRL_ID = 'Admin_Agents_Ctrl_Edit';

      Admin_Agents_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_Agents_Ctrl_Edit.DEPS = [];

      Admin_Agents_Ctrl_Edit.prototype.init = function() {
        this.agentId = parseInt(this.$stateParams.id);
        this.form = {};
        this.hasPermOverrides = false;
      };

      Admin_Agents_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendDataGet({
          agent: "/agents/" + this.agentId,
          teams: "/agent_teams",
          groups: "/agentgroups",
          groupPerms: "/agentgroups/permissions",
          notif_prefs_table: "/agents/" + this.agentId + "/notify-prefs/get-tables"
        }).then(function(result) {
          _this.agent = result.data.agent.agent;
          _this.teams = result.data.teams.agent_teams;
          _this.groups = result.data.groups.agentgroups;
          _this.groupPerms = result.data.groupPerms.groups;
          _this.agentNotifPrefsModel = new EditAgentNotifPrefs(result.data.notif_prefs_table);
          _this.notif_prefs = _this.agentNotifPrefsModel.prefsTable;
          _this.agentFormModel = new EditAgentModel(_this.agent, _this.groups, _this.teams);
          _this.form = _this.agentFormModel.form;
          _this.$scope.$watch('EditCtrl.form.agent_groups', function() {
            return _this.updateEffectiveUgPerms();
          }, true);
          _this.perm_form = _this.agent.perms;
          return _this.updateHasPermOverridesStatus();
        });
        return promise;
      };

      /*
      		# When usergroups are changed, we need to update the effective list of permissions
      */


      Admin_Agents_Ctrl_Edit.prototype.updateEffectiveUgPerms = function() {
        var group, groupIds, info, perms, pname, pval, type, _i, _j, _len, _len1, _ref1, _ref2, _ref3, _results;
        this.ugEffectivePerms = {
          ticket: {},
          people: {},
          org: {},
          chat: {},
          publish: {},
          general: {}
        };
        if (!this.form.agent_groups) {
          return;
        }
        groupIds = [];
        _ref1 = this.form.agent_groups;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          group = _ref1[_i];
          if (group.value) {
            groupIds.push(group.id);
          }
        }
        _ref2 = this.groupPerms;
        _results = [];
        for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
          info = _ref2[_j];
          if (_ref3 = info.group.id, __indexOf.call(groupIds, _ref3) >= 0) {
            _results.push((function() {
              var _ref4, _results1;
              _ref4 = info.perms;
              _results1 = [];
              for (type in _ref4) {
                if (!__hasProp.call(_ref4, type)) continue;
                perms = _ref4[type];
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
        	# When a permission is updated, we need to update the hasPermOverrides status.
        	# This is done by an ngChange on the permission toggles. We dont use a watch because
        	# it can become too slow to watch the large graph of permissions.
      */


      Admin_Agents_Ctrl_Edit.prototype.updateHasPermOverridesStatus = function() {
        var permName, perms, type, value, _ref1, _ref2;
        this.updateEffectiveUgPerms();
        this.hasPermOverrides = false;
        _ref1 = this.perm_form;
        for (type in _ref1) {
          if (!__hasProp.call(_ref1, type)) continue;
          perms = _ref1[type];
          for (permName in perms) {
            if (!__hasProp.call(perms, permName)) continue;
            value = perms[permName];
            if (value) {
              if ((((_ref2 = this.ugEffectivePerms[type]) != null ? _ref2[permName] : void 0) == null) || !this.ugEffectivePerms[type][permName]) {
                this.hasPermOverrides = true;
                return;
              }
            }
          }
        }
      };

      /*
        	# This does the actual removal of all perm overrides
      */


      Admin_Agents_Ctrl_Edit.prototype.clearPermOverrides = function() {
        var permName, perms, type, value, _ref1;
        _ref1 = this.perm_form;
        for (type in _ref1) {
          if (!__hasProp.call(_ref1, type)) continue;
          perms = _ref1[type];
          for (permName in perms) {
            if (!__hasProp.call(perms, permName)) continue;
            value = perms[permName];
            perms[permName] = false;
          }
        }
        return this.hasPermOverrides = false;
      };

      /*
        	# Shows the password reset modal
      */


      Admin_Agents_Ctrl_Edit.prototype.showResetPassword = function() {
        var doReset, inst,
          _this = this;
        doReset = function(setPassword) {
          if (!setPassword || !Strings.trim(setPassword)) {
            setPassword = '';
          }
          return _this.Api.sendPostJson("/agents/" + _this.agentId + "/reset-password", {
            set_password: setPassword
          });
        };
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
                var _this = this;
                $scope.is_saving = true;
                if ($scope.password.mode === 'set') {
                  return doReset($scope.password.manual).then(function() {
                    return $modalInstance.close();
                  });
                } else {
                  return doReset(false).then(function() {
                    return $modalInstance.close();
                  });
                }
              };
            }
          ]
        });
        return inst;
      };

      /*
        	# Shows the copy settings modal
      */


      Admin_Agents_Ctrl_Edit.prototype.showCopySettings = function() {
        var agents, copySettings, inst, _ref1, _ref2,
          _this = this;
        agents = (_ref1 = this.$scope.$parent) != null ? (_ref2 = _ref1.ListCtrl) != null ? _ref2.agents : void 0 : void 0;
        if (!agents) {
          return false;
        }
        if (agents.length === 1) {
          this.showAlert('There are no other agents to copy settings from');
          return false;
        }
        agents = agents.filter(function(x) {
          return x.id !== _this.agentId;
        });
        copySettings = function(settings) {
          var promise;
          promise = _this.Api.sendDataGet({
            agent: "/agents/" + settings.agent_id,
            notif_prefs_table: "/agents/" + _this.agentId + "/notify-prefs/get-tables",
            teams: "/agent_teams",
            groups: "/agentgroups"
          }).then(function(result) {
            var agent, agentFormModel, agentNotifPrefsModel, form, gids, group, groups, n, notif_prefs, permName, perms, team, teams, tids, type, value, _i, _j, _k, _l, _len, _len1, _len2, _len3, _len4, _len5, _m, _n, _ref10, _ref11, _ref12, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8, _ref9, _results;
            agent = result.data.agent.agent;
            teams = result.data.teams.agent_teams;
            groups = result.data.groups.agentgroups;
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
              _ref3 = form.teams;
              for (_i = 0, _len = _ref3.length; _i < _len; _i++) {
                team = _ref3[_i];
                if (team.value) {
                  tids.push(team.id);
                }
              }
              _ref4 = _this.form.teams;
              for (_j = 0, _len1 = _ref4.length; _j < _len1; _j++) {
                team = _ref4[_j];
                team.value = (_ref5 = team.id, __indexOf.call(tids, _ref5) >= 0);
              }
            }
            if (settings.groups) {
              gids = [];
              _ref6 = form.agent_groups;
              for (_k = 0, _len2 = _ref6.length; _k < _len2; _k++) {
                group = _ref6[_k];
                if (group.value) {
                  gids.push(group.id);
                }
              }
              _ref7 = _this.form.agent_groups;
              for (_l = 0, _len3 = _ref7.length; _l < _len3; _l++) {
                group = _ref7[_l];
                group.value = (_ref8 = group.id, __indexOf.call(gids, _ref8) >= 0);
              }
            }
            if (settings.perms) {
              _ref9 = agent.perms;
              for (type in _ref9) {
                if (!__hasProp.call(_ref9, type)) continue;
                perms = _ref9[type];
                for (permName in perms) {
                  if (!__hasProp.call(perms, permName)) continue;
                  value = perms[permName];
                  if (((_ref10 = _this.perm_form[type]) != null ? _ref10[permName] : void 0) == null) {
                    continue;
                  }
                  _this.perm_form[type][permName] = value;
                }
              }
            }
            if (settings.ticket_notifs) {
              _ref11 = ['sys_filters_email', 'sys_filters_alert', 'custom_filters_email', 'custom_filters_alert'];
              for (_m = 0, _len4 = _ref11.length; _m < _len4; _m++) {
                n = _ref11[_m];
                if ((_this.notif_prefs.subs[n] != null) && (notif_prefs.subs[n] != null)) {
                  _this.notif_prefs.subs[n] = notif_prefs.subs[n];
                }
              }
            }
            if (settings.other_notifs) {
              _ref12 = ['chat', 'task', 'twitter', 'feedback', 'publish', 'crm', 'account'];
              _results = [];
              for (_n = 0, _len5 = _ref12.length; _n < _len5; _n++) {
                n = _ref12[_n];
                if ((_this.notif_prefs.subs[n] != null) && (notif_prefs.subs[n] != null)) {
                  _results.push(_this.notif_prefs.subs[n] = notif_prefs.subs[n]);
                } else {
                  _results.push(void 0);
                }
              }
              return _results;
            }
          });
          return promise;
        };
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
        	# Shows the copy settings modal
      */


      Admin_Agents_Ctrl_Edit.prototype.showLoginAs = function() {
        var Api, agentId, agentName, inst,
          _this = this;
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
        return inst.result.then(function() {});
      };

      /*
        	# Shows the copy settings modal
      */


      Admin_Agents_Ctrl_Edit.prototype.showDelete = function() {
        var inst,
          _this = this;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Agents/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst.result.then(function() {});
      };

      /*
        	# Returns an object hash of the complete form data
      */


      Admin_Agents_Ctrl_Edit.prototype.getFormData = function() {
        var formData;
        formData = {
          agent: this.agentFormModel.getFormData(),
          filter_subs: this.agentNotifPrefsModel.getFilterSubs(),
          other_subs: this.agentNotifPrefsModel.getOtherSubs(),
          perm_overrides: this.perm_form
        };
        return formData;
      };

      /*
        	# Saves the agent
      */


      Admin_Agents_Ctrl_Edit.prototype.saveAgent = function() {
        var postData, promise,
          _this = this;
        this.startSpinner('saving');
        postData = this.getFormData();
        if (this.agentId) {
          promise = this.Api.sendPostJson("/agents/" + this.agentId, postData);
        } else {
          promise = this.Api.sendPutJson("/agents", postData);
        }
        promise.then(function() {
          return _this.stopSpinner('saving');
        });
        return promise;
      };

      return Admin_Agents_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/