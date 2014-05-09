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

      Admin_Agents_Ctrl_List.prototype.init = function() {};

      Admin_Agents_Ctrl_List.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          agents: '/agents',
          deleted_agents: '/agents/deleted'
        }).then((function(_this) {
          return function(result) {
            _this.agents = result.data.agents.agents;
            return _this.deletedCount = result.data.deleted_agents.agents.length;
          };
        })(this));
        return promise;
      };

      Admin_Agents_Ctrl_List.prototype.removeAgentFromList = function(id) {
        this.agents = this.agents.filter(function(x) {
          return x.id !== id;
        });
        return this.deletedCount++;
      };

      Admin_Agents_Ctrl_List.prototype.updateAgent = function(agent) {
        var a, _i, _len, _ref, _results;
        _ref = this.agents;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          a = _ref[_i];
          if (a.id === agent.id) {
            _results.push(a.display_name = agent.display_name);
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };

      Admin_Agents_Ctrl_List.prototype.addAgent = function(id, name) {
        return this.agents.push({
          id: id,
          display_name: name
        });
      };

      return Admin_Agents_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
