(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_Home, _ref;
    Admin_Main_Ctrl_Home = (function(_super) {
      __extends(Admin_Main_Ctrl_Home, _super);

      function Admin_Main_Ctrl_Home() {
        _ref = Admin_Main_Ctrl_Home.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Main_Ctrl_Home.CTRL_ID = 'Admin_Main_Ctrl_Home';

      Admin_Main_Ctrl_Home.CTRL_AS = 'Home';

      Admin_Main_Ctrl_Home.prototype.init = function() {
        this.online_agents = [];
        this.offline_agents = [];
      };

      Admin_Main_Ctrl_Home.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendDataGet({
          agents: '/agents'
        }).then(function(result) {
          var agent, data, _i, _len, _ref1, _results;
          data = result.data;
          _this.online_agents = [];
          _this.offline_agents = [];
          _ref1 = data.agents.agents;
          _results = [];
          for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
            agent = _ref1[_i];
            if (agent.is_online_now || agent.id === DP_PERSON_ID) {
              _results.push(_this.online_agents.push(agent));
            } else {
              _results.push(_this.offline_agents.push(agent));
            }
          }
          return _results;
        });
        return promise;
      };

      return Admin_Main_Ctrl_Home;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_Home.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Home.js.map
*/