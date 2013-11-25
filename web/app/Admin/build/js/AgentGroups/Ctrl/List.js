(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_AgentGroups_Ctrl_List, _ref;
    Admin_AgentGroups_Ctrl_List = (function(_super) {
      __extends(Admin_AgentGroups_Ctrl_List, _super);

      function Admin_AgentGroups_Ctrl_List() {
        _ref = Admin_AgentGroups_Ctrl_List.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_AgentGroups_Ctrl_List.CTRL_ID = 'Admin_AgentGroups_Ctrl_List';

      Admin_AgentGroups_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_AgentGroups_Ctrl_List.DEPS = [];

      Admin_AgentGroups_Ctrl_List.prototype.init = function() {};

      Admin_AgentGroups_Ctrl_List.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet('/agentgroups').then(function(result) {
          return _this.groups = result.data.agentgroups;
        });
        return promise;
      };

      return Admin_AgentGroups_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_AgentGroups_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=List.js.map
*/