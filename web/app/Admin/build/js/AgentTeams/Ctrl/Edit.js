(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_AgentTeams_Ctrl_Edit, _ref;
    Admin_AgentTeams_Ctrl_Edit = (function(_super) {
      __extends(Admin_AgentTeams_Ctrl_Edit, _super);

      function Admin_AgentTeams_Ctrl_Edit() {
        _ref = Admin_AgentTeams_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_AgentTeams_Ctrl_Edit.CTRL_ID = 'Admin_AgentTeams_Ctrl_Edit';

      Admin_AgentTeams_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_AgentTeams_Ctrl_Edit.DEPS = [];

      Admin_AgentTeams_Ctrl_Edit.prototype.init = function() {
        this.teamId = parseInt(this.$stateParams.id);
      };

      Admin_AgentTeams_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
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
        promise.then(function(res) {
          _this.agents = res.data.agents.agents;
          return _this.team = res.data.team.team;
        });
        return promise;
      };

      Admin_AgentTeams_Ctrl_Edit.prototype.saveForm = function() {
        var a, p, postData, _i, _len, _ref1,
          _this = this;
        postData = {
          name: this.team.name,
          person_ids: []
        };
        _ref1 = this.agents;
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          a = _ref1[_i];
          if (a.value) {
            postData.person_ids.push(a.id);
          }
        }
        if (this.teamId) {
          p = this.sendFormSaveApiCall('POST', "/agent_teams/" + this.teamId, postData);
        } else {
          p = this.sendFormSaveApiCall('PUT', "/agent_teams", postData);
        }
        p.then(function() {});
      };

      return Admin_AgentTeams_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_AgentTeams_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/