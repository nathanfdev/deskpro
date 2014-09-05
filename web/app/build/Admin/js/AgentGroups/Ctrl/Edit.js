(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_AgentGroups_Ctrl_Edit;
    Admin_AgentGroups_Ctrl_Edit = (function(_super) {
      __extends(Admin_AgentGroups_Ctrl_Edit, _super);

      function Admin_AgentGroups_Ctrl_Edit() {
        return Admin_AgentGroups_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_AgentGroups_Ctrl_Edit.CTRL_ID = 'Admin_AgentGroups_Ctrl_Edit';

      Admin_AgentGroups_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_AgentGroups_Ctrl_Edit.prototype.init = function() {
        this.groupId = parseInt(this.$stateParams.id);
        this.service = {
          groups: this.DataService.get('AgentGroups'),
          agents: this.DataService.get('Agents'),
          ticketDeps: this.DataService.get('TicketDeps'),
          chatDeps: this.DataService.get('ChatDeps')
        };
        this.all_perms = {
          perms: {},
          deps_perms: {
            tickets: {
              assign: true,
              full: true
            },
            chat: {
              full: true
            }
          }
        };
        this.$scope.toggleAgent = (function(_this) {
          return function(agent) {
            var groupIndex, index;
            index = _this.group.person_ids.indexOf(agent.id);
            groupIndex = agent.agentgroup_ids.indexOf(_this.group);
            if (index !== -1) {
              _this.group.person_ids.splice(index, 1);
              if (groupIndex !== -1) {
                return agent.agentgroup_ids.splice(groupIndex, 1);
              }
            } else {
              _this.group.person_ids.push(agent.id);
              if (groupIndex === -1) {
                return agent.agentgroup_ids.push(_this.group.id);
              }
            }
          };
        })(this);
      };

      Admin_AgentGroups_Ctrl_Edit.prototype.initialLoad = function() {
        var promises;
        promises = [this.service.groups.get(this.groupId), this.service.agents.all(), this.service.ticketDeps.all(), this.service.chatDeps.all()];
        return this.$q.all(promises).then((function(_this) {
          return function(res) {
            var dep, subdep, _i, _j, _len, _len1, _ref, _ref1;
            _this.group = res[0] || {
              id: 0
            };
            _this.agents = res[1];
            _this.chatDeps = res[3];
            _this.ticketDeps = [];
            _ref = res[2];
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              dep = _ref[_i];
              _this.ticketDeps.push(dep);
              if (dep.children) {
                _ref1 = dep.children;
                for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                  subdep = _ref1[_j];
                  subdep.depth = 1;
                  _this.ticketDeps.push(subdep);
                }
              }
            }
            _this.group.person_ids = [];
            _this.assignDepsPerms(_this.group);
            _this.updateAllPermsState();
            _this.agents.map(function(agent) {
              if (-1 !== agent.agentgroup_ids.indexOf(_this.group.id)) {
                return _this.group.person_ids.push(agent.id);
              }
            });
            if (_this.group.sys_name === 'agent_all_perms' || _this.group.sys_name === 'agent_all_safe_perms') {
              return _this.$scope.all_locked_perms = true;
            }
          };
        })(this));
      };

      Admin_AgentGroups_Ctrl_Edit.prototype.assignDepsPerms = function(group) {
        var assign, dep, full, u, _i, _j, _len, _len1, _ref, _ref1, _ref2, _ref3, _results;
        group.deps_perms = {
          tickets: {},
          chat: {}
        };
        _ref = this.ticketDeps;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          dep = _ref[_i];
          assign = false;
          full = false;
          if ((_ref1 = dep.permissions) != null ? _ref1.agentgroups : void 0) {
            u = dep.permissions.agentgroups.filter((function(_this) {
              return function(x) {
                return x.id === group.id;
              };
            })(this))[0];
            if (u) {
              if (u.name === 'full') {
                full = true;
              } else {
                assign = true;
              }
            }
          }
          group.deps_perms.tickets[dep.id] = {
            assign: assign,
            full: full
          };
        }
        _ref2 = this.chatDeps;
        _results = [];
        for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
          dep = _ref2[_j];
          full = false;
          if ((_ref3 = dep.permissions) != null ? _ref3.agentgroups : void 0) {
            u = dep.permissions.agentgroups.filter((function(_this) {
              return function(x) {
                return x.id === group.id;
              };
            })(this))[0];
            if (u) {
              full = true;
            }
          }
          _results.push(group.deps_perms.chat[dep.id] = {
            full: full
          });
        }
        return _results;
      };

      Admin_AgentGroups_Ctrl_Edit.prototype.changeAllPerms = function(type, section) {
        var dep, perm, _results, _results1;
        if (this.group == null) {
          return;
        }
        if ('perms' === type && this.group.perms[section]) {
          for (perm in this.group.perms[section]) {
            this.group.perms[section][perm] = this.all_perms[type][section];
          }
          if ('people' === section) {
            return this.changeAllPerms('perms', 'org');
          }
        } else if ('deps_perms_tickets' === type && this.group.deps_perms.tickets) {
          _results = [];
          for (dep in this.group.deps_perms.tickets) {
            _results.push(this.group.deps_perms.tickets[dep][section] = this.all_perms.deps_perms.tickets[section]);
          }
          return _results;
        } else if ('deps_perms_chat' === type && this.group.deps_perms.chat) {
          _results1 = [];
          for (dep in this.group.deps_perms.chat) {
            _results1.push(this.group.deps_perms.chat[dep][section] = this.all_perms.deps_perms.chat[section]);
          }
          return _results1;
        }
      };

      Admin_AgentGroups_Ctrl_Edit.prototype.updateAllPermsState = function() {
        var dep, enabled, perm, perms, section, sections, type, _ref, _ref1, _results;
        if (this.group == null) {
          return;
        }
        _ref = this.group.perms;
        for (section in _ref) {
          perms = _ref[section];
          enabled = true;
          for (perm in perms) {
            if (!perms[perm]) {
              enabled = false;
              break;
            }
          }
          this.all_perms.perms[section] = enabled;
        }
        if (!this.group.deps_perms) {
          return;
        }
        _ref1 = this.all_perms.deps_perms;
        _results = [];
        for (type in _ref1) {
          sections = _ref1[type];
          _results.push((function() {
            var _results1;
            _results1 = [];
            for (section in sections) {
              enabled = true;
              for (dep in this.group.deps_perms[type]) {
                if (!this.group.deps_perms[type][dep][section]) {
                  enabled = false;
                }
              }
              _results1.push(this.all_perms.deps_perms[type][section] = enabled);
            }
            return _results1;
          }).call(this));
        }
        return _results;
      };

      Admin_AgentGroups_Ctrl_Edit.prototype.saveForm = function() {
        var p, postData;
        postData = {
          group: this.group,
          dep_perms: this.group.deps_perms
        };
        if (this.groupId) {
          p = this.sendFormSaveApiCall('POST', "/agent_groups/" + this.groupId, postData);
        } else {
          p = this.sendFormSaveApiCall('PUT', "/agent_groups", postData);
        }
        p.then((function(_this) {
          return function(res) {
            _this.Growl.success(_this.getRegisteredMessage('saved_group'));
            if (!_this.groupId) {
              _this.groupId = _this.group.id = res.data.group_id;
              _this.service.groups._addModel(_this.group);
            }
            _this.$q.all([_this.service.ticketDeps.all(true), _this.service.chatDeps.all(true)]);
            return _this.$state.go('agents.groups.edit', {
              id: _this.groupId
            });
          };
        })(this));
      };

      Admin_AgentGroups_Ctrl_Edit.prototype.showDelete = function() {
        var deleteGroup, inst;
        deleteGroup = (function(_this) {
          return function() {
            return _this.service.groups.remove(_this.group).then(function() {
              _this.$q.all([_this.service.ticketDeps.all(true), _this.service.chatDeps.all(true)]);
              return _this.$state.go('agents.groups');
            });
          };
        })(this);
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('AgentGroups/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.doDelete = function(options) {
                $scope.is_loading = true;
                return deleteGroup().then(function() {
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

      Admin_AgentGroups_Ctrl_Edit.prototype.showCopySettings = function() {
        var copySettings, groups;
        groups = [];
        copySettings = (function(_this) {
          return function(settings) {
            return _this.service.groups.get(settings.group_id).then(function(group) {
              if (group == null) {
                return;
              }
              if (settings.copy_perms) {
                _this.group.perms = {};
                angular.copy(group.perms, _this.group.perms);
              }
              if (settings.copy_deps_perms) {
                _this.assignDepsPerms(group);
                _this.group.deps_perms = {};
                return angular.copy(group.deps_perms, _this.group.deps_perms);
              }
            });
          };
        })(this);
        return this.service.groups.all().then((function(_this) {
          return function(list) {
            var inst;
            list.map(function(group) {
              if (group !== _this.group) {
                return groups.push(group);
              }
            });
            if (!groups.length) {
              return _this.showAlert('There are no other groups to copy permissions from');
            }
            return inst = _this.$modal.open({
              templateUrl: _this.getTemplatePath('AgentGroups/copy-perms-modal.html'),
              controller: [
                '$scope', '$modalInstance', function($scope, $modalInstance) {
                  $scope.groups = groups;
                  $scope.options = {
                    group_id: groups[0].id,
                    copy_perms: true,
                    copy_deps_perms: true
                  };
                  $scope.dismiss = function() {
                    return $modalInstance.dismiss();
                  };
                  return $scope.doCopySettings = function() {
                    $scope.is_loading = true;
                    return copySettings($scope.options).then(function() {
                      return $modalInstance.dismiss();
                    });
                  };
                }
              ]
            });
          };
        })(this));
      };

      return Admin_AgentGroups_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_AgentGroups_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
