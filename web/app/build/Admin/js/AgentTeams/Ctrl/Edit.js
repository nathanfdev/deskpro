(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; },
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_AgentTeams_Ctrl_Edit;
    Admin_AgentTeams_Ctrl_Edit = (function(_super) {
      __extends(Admin_AgentTeams_Ctrl_Edit, _super);

      function Admin_AgentTeams_Ctrl_Edit() {
        return Admin_AgentTeams_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_AgentTeams_Ctrl_Edit.CTRL_ID = 'Admin_AgentTeams_Ctrl_Edit';

      Admin_AgentTeams_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_AgentTeams_Ctrl_Edit.DEPS = [];

      Admin_AgentTeams_Ctrl_Edit.prototype.init = function() {
        this.teamId = parseInt(this.$stateParams.id);
      };

      Admin_AgentTeams_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        if (this.teamId) {
          promise = this.Api.sendDataGet({
            team: "/agent_teams/" + this.teamId,
            agents: "/agents"
          });
        } else {
          promise = this.Api.sendDataGet({
            agents: "/agents"
          });
        }
        promise.then((function(_this) {
          return function(res) {
            var memberIds;
            _this.agents = res.data.agents.agents;
            _this.team = res.data.team.team;
            memberIds = _this.team.members.map(function(x) {
              return x.id;
            });
            return _this.agents.map(function(x) {
              var _ref;
              if (_ref = x.id, __indexOf.call(memberIds, _ref) >= 0) {
                return x.value = true;
              }
            });
          };
        })(this));
        return promise;
      };

      Admin_AgentTeams_Ctrl_Edit.prototype.saveForm = function() {
        var a, p, postData, _i, _len, _ref;
        postData = {
          team: {
            name: this.team.name,
            person_ids: []
          }
        };
        _ref = this.agents;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          a = _ref[_i];
          if (a.value) {
            postData.team.person_ids.push(a.id);
          }
        }
        if (this.teamId) {
          p = this.sendFormSaveApiCall('POST', "/agent_teams/" + this.teamId, postData);
        } else {
          p = this.sendFormSaveApiCall('PUT', "/agent_teams", postData);
        }
        p.then((function(_this) {
          return function(res) {
            _this.Growl.success(_this.getRegisteredMessage('saved_team'));
            if (_this.teamId) {
              return _this.getTeamListCtrl().renameTeamById(_this.teamId, _this.team.name);
            } else {
              _this.teamId = res.data.team_id;
              _this.getTeamListCtrl().addTeam({
                id: _this.teamId,
                name: _this.team.name
              });
              return _this.$state.go('agents.teams.edit', {
                id: _this.teamId
              });
            }
          };
        })(this));
      };


      /*
        	 * Shows the copy settings modal
       */

      Admin_AgentTeams_Ctrl_Edit.prototype.showDelete = function() {
        var deleteTeam, inst;
        deleteTeam = (function(_this) {
          return function() {
            var p;
            p = _this.Api.sendDelete("/agent_teams/" + _this.teamId);
            p.then(function() {
              _this.getTeamListCtrl().removeTeamById(_this.teamId);
              return _this.$state.go('agents.agents');
            });
            return p;
          };
        })(this);
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('AgentTeams/delete-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.doDelete = function(options) {
                $scope.is_loading = true;
                return deleteTeam().then(function() {
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

      Admin_AgentTeams_Ctrl_Edit.prototype.getTeamListCtrl = function() {
        var _ref;
        if (((_ref = this.$scope.$parent) != null ? _ref.ListCtrl : void 0) != null) {
          return this.$scope.$parent.ListCtrl;
        } else {
          return {
            addTeam: function() {},
            removeTeamById: function() {},
            renameTeamById: function() {}
          };
        }
      };

      return Admin_AgentTeams_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_AgentTeams_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
