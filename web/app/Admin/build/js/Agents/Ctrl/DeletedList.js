(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Agents_Ctrl_DeletedList, _ref;
    Admin_Agents_Ctrl_DeletedList = (function(_super) {
      __extends(Admin_Agents_Ctrl_DeletedList, _super);

      function Admin_Agents_Ctrl_DeletedList() {
        _ref = Admin_Agents_Ctrl_DeletedList.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Agents_Ctrl_DeletedList.CTRL_ID = 'Admin_Agents_Ctrl_DeletedList';

      Admin_Agents_Ctrl_DeletedList.CTRL_AS = 'ListCtrl';

      Admin_Agents_Ctrl_DeletedList.DEPS = [];

      Admin_Agents_Ctrl_DeletedList.prototype.init = function() {};

      Admin_Agents_Ctrl_DeletedList.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet('/agents/deleted').then(function(result) {
          return _this.agents = result.data.agents;
        });
        return promise;
      };

      Admin_Agents_Ctrl_DeletedList.prototype.removeAgentFromList = function(id) {
        return this.agents = this.agents.filter(function(x) {
          return x.id !== id;
        });
      };

      Admin_Agents_Ctrl_DeletedList.prototype.updateAgent = function(agent) {
        var a, _i, _len, _ref1, _results;
        _ref1 = this.agents;
        _results = [];
        for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
          a = _ref1[_i];
          if (a.id === agent.id) {
            _results.push(a.display_name = agent.display_name);
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      };

      Admin_Agents_Ctrl_DeletedList.prototype.addAgent = function(id, name) {
        return this.agents.push({
          id: id,
          display_name: name
        });
      };

      return Admin_Agents_Ctrl_DeletedList;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_DeletedList.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=DeletedList.js.map
*/