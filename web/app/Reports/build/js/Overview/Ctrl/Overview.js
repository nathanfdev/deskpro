(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base'], function(ReportsBaseCtrl) {
    var Reports_Overview_Ctrl_Overview, _ref;
    Reports_Overview_Ctrl_Overview = (function(_super) {
      __extends(Reports_Overview_Ctrl_Overview, _super);

      function Reports_Overview_Ctrl_Overview() {
        _ref = Reports_Overview_Ctrl_Overview.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Reports_Overview_Ctrl_Overview.CTRL_ID = 'Reports_Overview_Ctrl_Overview';

      Reports_Overview_Ctrl_Overview.CTRL_AS = 'Overview';

      Reports_Overview_Ctrl_Overview.DEPS = [];

      /*
      		#
      */


      Reports_Overview_Ctrl_Overview.prototype.init = function() {};

      /*
      		# Just doing all the necessary AJAX calls here
      */


      Reports_Overview_Ctrl_Overview.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendDataGet({
          tickets_status: "/reports/overview/data/tickets_status",
          tickets_awaiting_agent: "/reports/overview/data/tickets_awaiting_agent"
        }).then(function(res) {
          _this.$scope.tickets_status = res.data.tickets_status;
          _this.$scope.tickets_awaiting_agent = res.data.tickets_awaiting_agent;
          _this.setVariablesForTicketsStatuses();
          return _this.setVariablesForTicketsAwaitingAgent();
        });
        return this.$q.all([data_promise]);
      };

      /*
      		# We need to display bar graphs - so let's pre-calculate some variables
      */


      Reports_Overview_Ctrl_Overview.prototype.setVariablesForTicketsStatuses = function() {
        var denominator, key, percentage, _results;
        this.$scope.tickets_status.stats = [];
        denominator = this.$scope.tickets_status.max || 1;
        _results = [];
        for (key in this.$scope.tickets_status.titles) {
          if (!this.$scope.tickets_status.values[key]) {
            continue;
          }
          percentage = this.$scope.tickets_status.values[key] / denominator * 100;
          if (percentage < 1) {
            percentage = 1;
          }
          _results.push(this.$scope.tickets_status.stats.push({
            title: this.$scope.tickets_status.titles[key],
            value: this.$scope.tickets_status.values[key],
            left_percentage: percentage,
            right_percentage: 100 - percentage
          }));
        }
        return _results;
      };

      /*
      		# We need to display bar graphs - so let's pre-calculate some variables
      */


      Reports_Overview_Ctrl_Overview.prototype.setVariablesForTicketsAwaitingAgent = function() {
        var denominator, key, percentage, _results;
        this.$scope.tickets_awaiting_agent.stats = [];
        denominator = this.$scope.tickets_awaiting_agent.max || 1;
        _results = [];
        for (key in this.$scope.tickets_awaiting_agent.titles) {
          if (!this.$scope.tickets_awaiting_agent.values[key]) {
            continue;
          }
          percentage = this.$scope.tickets_awaiting_agent.values[key] / denominator * 100;
          if (percentage < 1) {
            percentage = 1;
          }
          _results.push(this.$scope.tickets_awaiting_agent.stats.push({
            title: this.$scope.tickets_awaiting_agent.titles[key],
            value: this.$scope.tickets_awaiting_agent.values[key],
            left_percentage: percentage,
            right_percentage: 100 - percentage
          }));
        }
        return _results;
      };

      return Reports_Overview_Ctrl_Overview;

    })(ReportsBaseCtrl);
    return Reports_Overview_Ctrl_Overview.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Overview.js.map
*/