(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Agents_Ctrl_DeletedRestore;
    Admin_Agents_Ctrl_DeletedRestore = (function(_super) {
      __extends(Admin_Agents_Ctrl_DeletedRestore, _super);

      function Admin_Agents_Ctrl_DeletedRestore() {
        return Admin_Agents_Ctrl_DeletedRestore.__super__.constructor.apply(this, arguments);
      }

      Admin_Agents_Ctrl_DeletedRestore.CTRL_ID = 'Admin_Agents_Ctrl_DeletedRestore';

      Admin_Agents_Ctrl_DeletedRestore.CTRL_AS = 'EditCtrl';

      Admin_Agents_Ctrl_DeletedRestore.prototype.init = function() {
        this.agentId = parseInt(this.$stateParams.id);
      };

      Admin_Agents_Ctrl_DeletedRestore.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          agent: "/agents/deleted/" + this.agentId
        });
        promise.then((function(_this) {
          return function(result) {
            return _this.agent = result.data.agent.agent;
          };
        })(this));
        return promise;
      };

      Admin_Agents_Ctrl_DeletedRestore.prototype.restoreAgent = function() {
        var promise;
        this.startSpinner('saving');
        promise = this.Api.sendPost("/agents/deleted/" + this.agentId + "/undelete");
        promise.then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true);
            return _this.$state.go('agents.agents.edit', {
              id: _this.agentId
            });
          };
        })(this));
        return promise;
      };

      return Admin_Agents_Ctrl_DeletedRestore;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_DeletedRestore.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=DeletedRestore.js.map
