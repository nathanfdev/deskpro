(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

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
      };

      Admin_AgentGroups_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        if (this.groupId) {
          promise = this.Api.sendDataGet({
            group: "/agent_groups/" + this.groupId,
            agents: "/agents",
            ticketDeps: "/ticket_deps?with_perms=1",
            chatDeps: "/chat_deps?with_perms=1"
          });
        } else {
          promise = this.Api.sendDataGet({
            agents: "/agents",
            ticketDeps: "/ticket_deps?with_perms=1",
            chatDeps: "/chat_deps?with_perms=1"
          });
        }
        promise.then((function(_this) {
          return function(res) {
            var assign, dep, full, memberIds, u, _i, _j, _len, _len1, _ref, _ref1, _ref2, _ref3, _results;
            _this.agents = res.data.agents.agents;
            if (_this.groupId) {
              _this.group = res.data.group.group;
            } else {
              _this.group = {
                id: 0,
                title: '',
                members: []
              };
            }
            memberIds = _this.group.members.map(function(x) {
              return x.id;
            });
            _this.agents.map(function(x) {
              var _ref;
              if (_ref = x.id, __indexOf.call(memberIds, _ref) >= 0) {
                return x.value = true;
              }
            });
            _this.perm_form = _this.group.perms;
            _this.ticketDeps = res.data.ticketDeps.departments;
            _this.chatDeps = res.data.chatDeps.departments;
            _this.deps_perms = {
              tickets: {},
              chat: {}
            };
            if (_this.groupId) {
              _ref = _this.ticketDeps;
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                dep = _ref[_i];
                assign = false;
                full = false;
                if ((_ref1 = dep.permissions) != null ? _ref1.agentgroups : void 0) {
                  u = dep.permissions.agentgroups.filter(function(x) {
                    return x.id === _this.groupId;
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
                if ((_ref3 = dep.permissions) != null ? _ref3.agentgroups : void 0) {
                  u = dep.permissions.agentgroups.filter(function(x) {
                    return x.id === _this.groupId;
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
            }
          };
        })(this));
        return promise;
      };

      Admin_AgentGroups_Ctrl_Edit.prototype.saveForm = function() {
        var a, p, postData, _i, _len, _ref;
        postData = {
          group: {
            title: this.group.title,
            perms: this.perm_form,
            person_ids: []
          },
          dep_perms: this.deps_perms
        };
        _ref = this.agents;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          a = _ref[_i];
          if (a.value) {
            postData.group.person_ids.push(a.id);
          }
        }
        if (this.groupId) {
          p = this.sendFormSaveApiCall('POST', "/agent_groups/" + this.groupId, postData);
        } else {
          p = this.sendFormSaveApiCall('PUT', "/agent_groups", postData);
        }
        p.then((function(_this) {
          return function(res) {
            _this.Growl.success(_this.getRegisteredMessage('saved_group'));
            if (_this.groupId) {
              return _this.getGroupListCtrl().renameGroupById(_this.groupId, _this.group.title);
            } else {
              _this.groupId = res.data.group_id;
              _this.getGroupListCtrl().addGroup({
                id: _this.groupId,
                title: _this.group.title
              });
              return _this.$state.go('agents.groups.edit', {
                id: _this.groupId
              });
            }
          };
        })(this));
      };


      /*
        	 * Shows the copy settings modal
       */

      Admin_AgentGroups_Ctrl_Edit.prototype.showDelete = function() {
        var deleteGroup, inst;
        deleteGroup = (function(_this) {
          return function() {
            var p;
            p = _this.Api.sendDelete("/agent_groups/" + _this.groupId);
            p.then(function() {
              _this.getGroupListCtrl().removeGroupById(_this.groupId);
              return _this.$state.go('agents.agents');
            });
            return p;
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
        	 * Gets a reference to the parent list view which we need to update with the new details
       */

      Admin_AgentGroups_Ctrl_Edit.prototype.getGroupListCtrl = function() {
        var _ref;
        if (((_ref = this.$scope.$parent) != null ? _ref.ListCtrl : void 0) != null) {
          return this.$scope.$parent.ListCtrl;
        } else {
          return {
            addGroup: function() {},
            removeGroupById: function() {},
            renameGroupById: function() {}
          };
        }
      };

      return Admin_AgentGroups_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_AgentGroups_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
