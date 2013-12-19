(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Agents_Ctrl_Edit, _ref;
    Admin_Agents_Ctrl_Edit = (function(_super) {
      __extends(Admin_Agents_Ctrl_Edit, _super);

      function Admin_Agents_Ctrl_Edit() {
        _ref = Admin_Agents_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Agents_Ctrl_Edit.CTRL_ID = 'Admin_Agents_Ctrl_Edit';

      Admin_Agents_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_Agents_Ctrl_Edit.DEPS = [];

      Admin_Agents_Ctrl_Edit.prototype.init = function() {
        this.form = {};
      };

      Admin_Agents_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendDataGet({
          agent: "/agents/" + this.$stateParams.id,
          teams: "/agent_teams",
          groups: "/agentgroups"
        }).then(function(result) {
          _this.agent = result.data.agent.agent;
          _this.teams = result.data.teams.agent_teams;
          return _this.groups = result.data.groups.agentgroups;
        });
      };

      return Admin_Agents_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/