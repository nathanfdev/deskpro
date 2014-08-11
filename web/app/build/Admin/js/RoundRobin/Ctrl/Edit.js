(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_RoundRobin_Ctrl_Edit;
    Admin_RoundRobin_Ctrl_Edit = (function(_super) {
      __extends(Admin_RoundRobin_Ctrl_Edit, _super);

      function Admin_RoundRobin_Ctrl_Edit() {
        return Admin_RoundRobin_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_RoundRobin_Ctrl_Edit.CTRL_ID = 'Admin_RoundRobin_Ctrl_Edit';

      Admin_RoundRobin_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_RoundRobin_Ctrl_Edit.DEPS = ['$stateParams', 'Growl', '$timeout'];

      Admin_RoundRobin_Ctrl_Edit.prototype.init = function() {
        this.robin = {};
        this.agents = [];
        this.nextAgentInQueue = null;
        this.bulk = null;
        this.service = this.DataService.get('RoundRobin');
        this.serviceAgents = this.DataService.get('Agents');
        this.serviceDeps = this.DataService.get('TicketDeps');
        this.serviceGroups = this.DataService.get('AgentGroups');
        this.serviceTeams = this.DataService.get('AgentTeams');
        this.groups = [];
        this.teams = [];
        this.deps = [];
        return this.sortedListOptions = {
          axis: 'y',
          items: 'li.sortable',
          handle: '.drag-handle'
        };
      };

      Admin_RoundRobin_Ctrl_Edit.prototype.initialLoad = function() {
        var promises;
        this.service.loadList(true);
        promises = [this.serviceAgents.all(), this.service.get(parseInt(this.$stateParams.id || 0)), this.serviceDeps.all(), this.serviceGroups.all(), this.serviceTeams.all()];
        return this.$q.all(promises).then((function(_this) {
          return function(res) {
            _this.agents = res[0];
            _this.mapFormModel(res[1]);
            _this.deps = res[2];
            _this.groups = res[3];
            return _this.teams = res[4];
          };
        })(this));
      };

      Admin_RoundRobin_Ctrl_Edit.prototype.mapFormModel = function(model) {
        var promises;
        this.robin.agents = [];
        if (model == null) {
          return;
        }
        this.robin.id = model.id;
        this.robin.title = model.title;
        if (model.next != null) {
          this.serviceAgents.get(model.next.id).then((function(_this) {
            return function(agent) {
              return _this.robin.next = agent;
            };
          })(this));
        }
        promises = [];
        model.agents.map((function(_this) {
          return function(data) {
            var promise;
            promise = _this.serviceAgents.get(data.id).then(function(agent) {
              return _this.robin.agents.push(agent);
            });
            return promises.push(promise);
          };
        })(this));
        return this.$q.all(promises).then((function(_this) {
          return function() {
            return _this.sortAgents();
          };
        })(this));
      };

      Admin_RoundRobin_Ctrl_Edit.prototype.sortAgents = function() {
        return this.agents.sort((function(_this) {
          return function(a, b) {
            var indexA, indexB;
            indexA = _this.robin.agents.indexOf(a);
            indexB = _this.robin.agents.indexOf(b);
            if (indexA === indexB) {
              return 0;
            }
            if (indexA === -1) {
              return 1;
            }
            if (indexB === -1) {
              return -1;
            }
            if (indexA < indexB) {
              return -1;
            } else {
              return 1;
            }
          };
        })(this));
      };

      Admin_RoundRobin_Ctrl_Edit.prototype.handleAgent = function(agent) {
        var index;
        if (this.agents.indexOf(agent) === -1) {
          return;
        }
        index = this.robin.agents.indexOf(agent);
        if (index === -1) {
          return this.robin.agents.unshift(agent);
        } else {
          return this.robin.agents.splice(index, 1);
        }
      };

      Admin_RoundRobin_Ctrl_Edit.prototype.handleBulk = function() {
        var add, params;
        if (this.bulk == null) {
          return;
        }
        params = this.bulk.split('.');
        add = {};
        switch (params[0]) {
          case 'd':
            return this.serviceDeps.get(params[1]).then((function(_this) {
              return function(dep) {
                var agent, agentData, agentGroupId, agentgroup, id, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref2, _ref3;
                _ref = _this.agents;
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                  agent = _ref[_i];
                  if (!(_this.robin.agents.indexOf(agent) === -1)) {
                    continue;
                  }
                  _ref1 = dep.permissions.users;
                  for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                    agentData = _ref1[_j];
                    if ('full' === agentData.name && agent.id === agentData.id) {
                      add[agent.id] = agent;
                    }
                  }
                  _ref2 = dep.permissions.agentgroups;
                  for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
                    agentgroup = _ref2[_k];
                    if (agentgroup.name === 'full') {
                      _ref3 = agent.agentgroup_ids;
                      for (_l = 0, _len3 = _ref3.length; _l < _len3; _l++) {
                        agentGroupId = _ref3[_l];
                        if (!(agentGroupId === agentgroup.id)) {
                          continue;
                        }
                        add[agent.id] = agent;
                        break;
                      }
                    }
                  }
                }
                for (id in add) {
                  agent = add[id];
                  _this.handleAgent(agent);
                }
                return _this.sortAgents();
              };
            })(this));
          case 'g':
            return this.serviceGroups.get(params[1]).then((function(_this) {
              return function(group) {
                var agent, agentGroupId, id, _i, _j, _len, _len1, _ref, _ref1;
                _ref = _this.agents;
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                  agent = _ref[_i];
                  if (_this.robin.agents.indexOf(agent) === -1) {
                    _ref1 = agent.agentgroup_ids;
                    for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                      agentGroupId = _ref1[_j];
                      if (agentGroupId === group.id) {
                        add[agent.id] = agent;
                      }
                    }
                  }
                }
                for (id in add) {
                  agent = add[id];
                  _this.handleAgent(agent);
                }
                return _this.sortAgents();
              };
            })(this));
          case 't':
            return this.serviceTeams.get(params[1]).then((function(_this) {
              return function(team) {
                var agent, agentTeam, id, _i, _j, _len, _len1, _ref, _ref1;
                _ref = _this.agents;
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                  agent = _ref[_i];
                  if (_this.robin.agents.indexOf(agent) === -1) {
                    _ref1 = agent.teams;
                    for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                      agentTeam = _ref1[_j];
                      if (agentTeam.id === team.id) {
                        add[agent.id] = agent;
                      }
                    }
                  }
                }
                for (id in add) {
                  agent = add[id];
                  _this.handleAgent(agent);
                }
                return _this.sortAgents();
              };
            })(this));
        }
      };

      Admin_RoundRobin_Ctrl_Edit.prototype.save = function() {
        if (this.$scope.Form.$invalid) {
          return;
        }
        this.startSpinner('saving');
        return this.service.set(this.robin).then((function(_this) {
          return function(model) {
            _this.stopSpinner('saving');
            _this.mapFormModel(model);
            _this.$state.go('tickets.roundrobin');
            return _this.Growl.success('Saved');
          };
        })(this), (function(_this) {
          return function(res) {
            _this.stopSpinner('saving');
            return _this.Growl.error(res.info);
          };
        })(this));
      };

      Admin_RoundRobin_Ctrl_Edit.prototype["delete"] = function() {
        return this.service.checkTriggers(this.robin.id).then((function(_this) {
          return function(data) {
            _this.active_triggers = data.active_triggers;
            return _this.$timeout(function() {
              var msg, state, title, _del;
              title = _this.getRegisteredMessage('modal_title');
              msg = _this.getRegisteredMessage('modal_message');
              state = _this.$state;
              _del = function(modal) {
                return _this.service.remove(_this.robin).then(function() {
                  modal.dismiss();
                  return state.go('tickets.roundrobin');
                });
              };
              return _this.$modal.open({
                templateUrl: _this.getTemplatePath('Index/modal-confirm.html'),
                controller: [
                  '$scope', '$modalInstance', function($scope, $modalInstance) {
                    $scope.title = title;
                    $scope.message = msg;
                    $scope.dismiss = function() {
                      return $modalInstance.dismiss();
                    };
                    return $scope.confirm = function() {
                      return _del($modalInstance);
                    };
                  }
                ]
              });
            }, 1);
          };
        })(this));
      };

      return Admin_RoundRobin_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_RoundRobin_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
