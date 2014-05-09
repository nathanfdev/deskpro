(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'moment'], function(ReportsBaseCtrl, moment) {
    var Reports_AgentActivity_Ctrl_AgentActivity;
    Reports_AgentActivity_Ctrl_AgentActivity = (function(_super) {
      __extends(Reports_AgentActivity_Ctrl_AgentActivity, _super);

      function Reports_AgentActivity_Ctrl_AgentActivity() {
        return Reports_AgentActivity_Ctrl_AgentActivity.__super__.constructor.apply(this, arguments);
      }

      Reports_AgentActivity_Ctrl_AgentActivity.CTRL_ID = 'Reports_AgentActivity_Ctrl_AgentActivity';

      Reports_AgentActivity_Ctrl_AgentActivity.CTRL_AS = 'AgentActivity';

      Reports_AgentActivity_Ctrl_AgentActivity.DEPS = ['Api', '$sce'];


      /*
      		 * Initializing..
       */

      Reports_AgentActivity_Ctrl_AgentActivity.prototype.init = function() {
        this.html = '';
        this.date = new Date();
        this.all_agents = [];
        this.agent_teams = [];
        this.filter = {};
        this.filter.date = moment(this.date).format("YYYY-MM-DD");
        return this.filter.agent_or_team = 'all';
      };


      /*
      		 * Just doing all the necessary AJAX calls here
       */

      Reports_AgentActivity_Ctrl_AgentActivity.prototype.initialLoad = function() {
        return this.loadResults();
      };


      /*
      		 * This method updates current parameters that are used for sending request to API
       */

      Reports_AgentActivity_Ctrl_AgentActivity.prototype.updateFilter = function() {
        this.filter.date = moment(this.date).format("YYYY-MM-DD");
        return this.loadResults();
      };


      /*
      		 * Loading the results of sending request to API
       */

      Reports_AgentActivity_Ctrl_AgentActivity.prototype.loadResults = function() {
        var promise;
        this.startSpinner('loading_results');
        promise = this.Api.sendGet("/reports/agent-activity/" + this.filter.agent_or_team + "/" + this.filter.date).then((function(_this) {
          return function(res) {
            _this.html = _this.$sce.trustAsHtml(res.data.html);
            _this.all_agents = res.data.all_agents;
            _this.agent_teams = res.data.agent_teams;
            return _this.stopSpinner('loading_results', true);
          };
        })(this));
        return promise;
      };

      return Reports_AgentActivity_Ctrl_AgentActivity;

    })(ReportsBaseCtrl);
    return Reports_AgentActivity_Ctrl_AgentActivity.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=AgentActivity.js.map
