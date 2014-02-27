(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_AgentTeams_Ctrl_List;
    Admin_AgentTeams_Ctrl_List = (function(_super) {
      __extends(Admin_AgentTeams_Ctrl_List, _super);

      function Admin_AgentTeams_Ctrl_List() {
        return Admin_AgentTeams_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_AgentTeams_Ctrl_List.CTRL_ID = 'Admin_AgentTeams_Ctrl_List';

      Admin_AgentTeams_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_AgentTeams_Ctrl_List.DEPS = [];

      Admin_AgentTeams_Ctrl_List.prototype.init = function() {};

      Admin_AgentTeams_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet('/agent_teams').then((function(_this) {
          return function(result) {
            return _this.teams = result.data.agent_teams;
          };
        })(this));
        return promise;
      };

      Admin_AgentTeams_Ctrl_List.prototype.addTeam = function(team) {
        return this.teams.push(team);
      };

      Admin_AgentTeams_Ctrl_List.prototype.removeTeamById = function(teamId) {
        teamId = parseInt(teamId);
        return this.teams = this.teams.filter(function(x) {
          return x.id !== teamId;
        });
      };

      Admin_AgentTeams_Ctrl_List.prototype.renameTeamById = function(teamId, name) {
        return this.teams.filter(function(x) {
          return x.id === teamId;
        }).map(function(x) {
          return x.name = name;
        });
      };

      return Admin_AgentTeams_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_AgentTeams_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
