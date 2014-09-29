(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(Admin_Main_DataService_BaseListEdit) {
    var Admin_AgentGroups_DataService_AgentGroups;
    return Admin_AgentGroups_DataService_AgentGroups = (function(_super) {
      __extends(Admin_AgentGroups_DataService_AgentGroups, _super);

      function Admin_AgentGroups_DataService_AgentGroups() {
        return Admin_AgentGroups_DataService_AgentGroups.__super__.constructor.apply(this, arguments);
      }

      Admin_AgentGroups_DataService_AgentGroups.$inject = ['Api', '$q'];

      Admin_AgentGroups_DataService_AgentGroups.prototype.url = function() {
        return 'agent_groups';
      };

      Admin_AgentGroups_DataService_AgentGroups.prototype.resolveResponse = function(response) {
        return response.groups;
      };

      Admin_AgentGroups_DataService_AgentGroups.prototype.all = function() {
        return Admin_AgentGroups_DataService_AgentGroups.__super__.all.call(this, false, {
          with_perms: 1
        });
      };

      return Admin_AgentGroups_DataService_AgentGroups;

    })(Admin_Main_DataService_BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=AgentGroups.js.map
