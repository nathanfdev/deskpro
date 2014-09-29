(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Agents_Ctrl_List;
    Admin_Agents_Ctrl_List = (function(_super) {
      __extends(Admin_Agents_Ctrl_List, _super);

      function Admin_Agents_Ctrl_List() {
        return Admin_Agents_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_Agents_Ctrl_List.CTRL_ID = 'Admin_Agents_Ctrl_List';

      Admin_Agents_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_Agents_Ctrl_List.DEPS = [];

      Admin_Agents_Ctrl_List.prototype.init = function() {
        this.service = {
          agents: this.DataService.get('Agents')
        };
      };

      Admin_Agents_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        this.service.agents.all().then((function(_this) {
          return function(agents) {
            return _this.agents = agents;
          };
        })(this));
        promise = this.Api.sendDataGet({
          deleted_agents: '/agents/deleted'
        }).then((function(_this) {
          return function(result) {
            return _this.deletedCount = result.data.deleted_agents.agents.length;
          };
        })(this));
        return promise;
      };

      Admin_Agents_Ctrl_List.prototype.removeAgentFromList = function(id) {
        return this.deletedCount++;
      };

      return Admin_Agents_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
