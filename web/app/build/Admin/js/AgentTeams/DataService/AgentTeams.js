(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(Admin_Main_DataService_BaseListEdit) {
    var Admin_AgentTeams_DataService_AgentTeams;
    return Admin_AgentTeams_DataService_AgentTeams = (function(_super) {
      __extends(Admin_AgentTeams_DataService_AgentTeams, _super);

      function Admin_AgentTeams_DataService_AgentTeams() {
        return Admin_AgentTeams_DataService_AgentTeams.__super__.constructor.apply(this, arguments);
      }

      Admin_AgentTeams_DataService_AgentTeams.$inject = ['Api', '$q'];

      Admin_AgentTeams_DataService_AgentTeams.prototype.url = function() {
        return '/agent_teams';
      };

      Admin_AgentTeams_DataService_AgentTeams.prototype.resolveResponse = function(response) {
        return response.agent_teams;
      };

      return Admin_AgentTeams_DataService_AgentTeams;

    })(Admin_Main_DataService_BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=AgentTeams.js.map
