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
          tickets_awaiting_agent: "/reports/overview/data/tickets_awaiting_agent",
          tickets_user_waiting_time: "/reports/overview/data/tickets_user_waiting_time",
          tickets_resolved: "/reports/overview/data/tickets_resolved",
          tickets_response_time: "/reports/overview/data/tickets_response_time",
          tickets_sla_status: "/reports/overview/data/tickets_sla_status",
          tickets_opened_hour: "/reports/overview/data/tickets_opened_hour",
          chats_created: "/reports/overview/data/chats_created"
        }).then(function(res) {
          _this.$scope.tickets_status = res.data.tickets_status;
          _this.$scope.tickets_awaiting_agent = res.data.tickets_awaiting_agent;
          _this.$scope.tickets_user_waiting_time = res.data.tickets_user_waiting_time;
          _this.$scope.tickets_resolved = res.data.tickets_resolved;
          _this.$scope.tickets_response_time = res.data.tickets_response_time;
          _this.$scope.tickets_sla_status = res.data.tickets_sla_status;
          _this.$scope.tickets_opened_hour = res.data.tickets_opened_hour;
          _this.$scope.chats_created = res.data.chats_created;
          _this.setVariablesForTicketsStatuses();
          _this.setVariablesForTicketsAwaitingAgent();
          _this.setVariablesForTicketsUserWaitingTime();
          _this.setVariablesForTicketsResolved();
          _this.setVariablesForTicketsResponseTime();
          _this.setVariablesForTicketsSlaStatus();
          _this.setVariablesForTicketsOpenedHours();
          return _this.setVariablesForChatsCreated();
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
            value: this.$scope.tickets_status.values[key] || 0,
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
            value: this.$scope.tickets_awaiting_agent.values[key] || 0,
            left_percentage: percentage,
            right_percentage: 100 - percentage
          }));
        }
        return _results;
      };

      /*
      		# We need to display bar graphs - so let's pre-calculate some variables
      */


      Reports_Overview_Ctrl_Overview.prototype.setVariablesForChatsCreated = function() {
        var denominator, key, percentage, _results;
        this.$scope.chats_created.stats = [];
        denominator = this.$scope.chats_created.max || 1;
        _results = [];
        for (key in this.$scope.chats_created.titles) {
          if (!this.$scope.chats_created.values[key]) {
            continue;
          }
          percentage = this.$scope.chats_created.values[key] / denominator * 100;
          if (percentage < 1) {
            percentage = 1;
          }
          _results.push(this.$scope.chats_created.stats.push({
            title: this.$scope.chats_created.titles[key],
            value: this.$scope.chats_created.values[key] || 0,
            left_percentage: percentage,
            right_percentage: 100 - percentage
          }));
        }
        return _results;
      };

      /*
      		# We need to display bar graphs - so let's pre-calculate some variables
      */


      Reports_Overview_Ctrl_Overview.prototype.setVariablesForTicketsResolved = function() {
        var denominator, key, percentage, _results;
        this.$scope.tickets_resolved.stats = [];
        denominator = this.$scope.tickets_resolved.max || 1;
        _results = [];
        for (key in this.$scope.tickets_resolved.titles) {
          if (!this.$scope.tickets_resolved.values[key]) {
            continue;
          }
          percentage = this.$scope.tickets_resolved.values[key] / denominator * 100;
          if (percentage < 1) {
            percentage = 1;
          }
          _results.push(this.$scope.tickets_resolved.stats.push({
            title: this.$scope.tickets_resolved.titles[key],
            value: this.$scope.tickets_resolved.values[key] || 0,
            left_percentage: percentage,
            right_percentage: 100 - percentage
          }));
        }
        return _results;
      };

      /*
      		# We need to display bar graphs - so let's pre-calculate some variables
      */


      Reports_Overview_Ctrl_Overview.prototype.setVariablesForTicketsSlaStatus = function() {
        var denominator, key, percentage, _results;
        this.$scope.tickets_sla_status.stats = [];
        denominator = this.$scope.tickets_sla_status.max || 1;
        _results = [];
        for (key in this.$scope.tickets_sla_status.titles) {
          if (!this.$scope.tickets_sla_status.values[key]) {
            continue;
          }
          percentage = this.$scope.tickets_sla_status.values[key] / denominator * 100;
          if (percentage < 1) {
            percentage = 1;
          }
          _results.push(this.$scope.tickets_sla_status.stats.push({
            title: this.$scope.tickets_sla_status.titles[key],
            value: this.$scope.tickets_sla_status.values[key] || 0,
            left_percentage: percentage,
            right_percentage: 100 - percentage
          }));
        }
        return _results;
      };

      /*
      		# We need to display bar graphs - so let's pre-calculate some variables
      */


      Reports_Overview_Ctrl_Overview.prototype.setVariablesForTicketsOpenedHours = function() {
        var denominator, key, percentage, _results;
        this.$scope.tickets_opened_hour.stats = [];
        this.$scope.tickets_opened_hour.column_width = 100 / Object.keys(this.$scope.tickets_opened_hour.titles).length;
        denominator = this.$scope.tickets_opened_hour.max || 1;
        _results = [];
        for (key in this.$scope.tickets_opened_hour.titles) {
          if (this.$scope.tickets_opened_hour.values[key]) {
            percentage = this.$scope.tickets_opened_hour.values[key] / denominator * 100;
            if (percentage < 1) {
              percentage = 1;
            }
            _results.push(this.$scope.tickets_opened_hour.stats.push({
              title: this.$scope.tickets_opened_hour.titles[key],
              value: this.$scope.tickets_opened_hour.values[key] || 0,
              percentage: percentage
            }));
          } else {
            _results.push(this.$scope.tickets_opened_hour.stats.push({
              title: this.$scope.tickets_opened_hour.titles[key]
            }));
          }
        }
        return _results;
      };

      /*
      		# We need to display bar graphs - so let's pre-calculate some variables
      		# What is special here - we display every piece of data
      		# Just for cases with no data we display only labels without graphical bars
      		# Ie. if we have 0 tickets created < 5 minutes ago, we still display '< 5 minutes' label, but without bar
      		# This leads to the situation that we have to iterate over all the '@$scope.tickets_user_waiting_time.titles' array
      */


      Reports_Overview_Ctrl_Overview.prototype.setVariablesForTicketsUserWaitingTime = function() {
        var denominator, key, percentage, sub_percentage, sub_stats, subid, subtitle, _ref1, _results;
        this.$scope.tickets_user_waiting_time.stats = [];
        denominator = this.$scope.tickets_user_waiting_time.max || 1;
        _results = [];
        for (key in this.$scope.tickets_user_waiting_time.titles) {
          percentage = this.$scope.tickets_user_waiting_time.values[key] / denominator * 100;
          if (percentage < 1) {
            percentage = 1;
          }
          if (!this.$scope.tickets_user_waiting_time.sub_titles) {
            if (this.$scope.tickets_user_waiting_time.values[key]) {
              _results.push(this.$scope.tickets_user_waiting_time.stats.push({
                title: this.$scope.tickets_user_waiting_time.titles[key],
                value: this.$scope.tickets_user_waiting_time.values[key] || 0,
                percentage: percentage
              }));
            } else {
              _results.push(this.$scope.tickets_user_waiting_time.stats.push({
                title: this.$scope.tickets_user_waiting_time.titles[key]
              }));
            }
          } else {
            percentage = this.$scope.tickets_user_waiting_time.group_total[key] / denominator * 100;
            if (percentage < 1) {
              percentage = 1;
            }
            if (this.$scope.tickets_user_waiting_time.group_total[key]) {
              sub_stats = [];
              _ref1 = this.$scope.tickets_user_waiting_time.sub_titles;
              for (subid in _ref1) {
                subtitle = _ref1[subid];
                if (!this.$scope.tickets_user_waiting_time.values[key][subid]) {
                  continue;
                }
                sub_percentage = this.$scope.tickets_user_waiting_time.values[key][subid] / this.$scope.tickets_user_waiting_time.group_total[key] * 100;
                if (sub_percentage < 1) {
                  sub_percentage = 1;
                }
                sub_stats.push({
                  title: subtitle + ' (' + this.$scope.tickets_user_waiting_time.values[key][subid] + ')',
                  percentage: sub_percentage,
                  background: this.$scope.tickets_user_waiting_time.group_keys[subid]
                });
              }
              _results.push(this.$scope.tickets_user_waiting_time.stats.push({
                title: this.$scope.tickets_user_waiting_time.titles[key],
                value: this.$scope.tickets_user_waiting_time.group_total[key] || 0,
                percentage: percentage,
                sub_stats: sub_stats
              }));
            } else {
              _results.push(this.$scope.tickets_user_waiting_time.stats.push({
                title: this.$scope.tickets_user_waiting_time.titles[key]
              }));
            }
          }
        }
        return _results;
      };

      /*
      		# We need to display bar graphs - so let's pre-calculate some variables
      		# What is special here - we display every piece of data
      		# Just for cases with no data we display only labels without graphical bars
      		# Ie. if we have 0 tickets created < 5 minutes ago, we still display '< 5 minutes' label, but without bar
      		# This leads to the situation that we have to iterate over all the '@$scope.tickets_user_waiting_time.titles' array
      */


      Reports_Overview_Ctrl_Overview.prototype.setVariablesForTicketsResponseTime = function() {
        var denominator, key, percentage, sub_percentage, sub_stats, subid, subtitle, _ref1, _results;
        this.$scope.tickets_response_time.stats = [];
        denominator = this.$scope.tickets_response_time.max || 1;
        _results = [];
        for (key in this.$scope.tickets_response_time.titles) {
          percentage = this.$scope.tickets_response_time.values[key] / denominator * 100;
          if (percentage < 1) {
            percentage = 1;
          }
          if (!this.$scope.tickets_response_time.sub_titles) {
            if (this.$scope.tickets_response_time.values[key]) {
              _results.push(this.$scope.tickets_response_time.stats.push({
                title: this.$scope.tickets_response_time.titles[key],
                value: this.$scope.tickets_response_time.values[key] || 0,
                percentage: percentage
              }));
            } else {
              _results.push(this.$scope.tickets_response_time.stats.push({
                title: this.$scope.tickets_response_time.titles[key]
              }));
            }
          } else {
            percentage = this.$scope.tickets_response_time.group_total[key] / denominator * 100;
            if (percentage < 1) {
              percentage = 1;
            }
            if (this.$scope.tickets_response_time.group_total[key]) {
              sub_stats = [];
              _ref1 = this.$scope.tickets_response_time.sub_titles;
              for (subid in _ref1) {
                subtitle = _ref1[subid];
                if (!this.$scope.tickets_response_time.values[key][subid]) {
                  continue;
                }
                sub_percentage = this.$scope.tickets_response_time.values[key][subid] / this.$scope.tickets_response_time.group_total[key] * 100;
                if (sub_percentage < 1) {
                  sub_percentage = 1;
                }
                sub_stats.push({
                  title: subtitle + ' (' + this.$scope.tickets_response_time.values[key][subid] + ')',
                  percentage: sub_percentage,
                  background: this.$scope.tickets_response_time.group_keys[subid]
                });
              }
              _results.push(this.$scope.tickets_response_time.stats.push({
                title: this.$scope.tickets_response_time.titles[key],
                value: this.$scope.tickets_response_time.group_total[key] || 0,
                percentage: percentage,
                sub_stats: sub_stats
              }));
            } else {
              _results.push(this.$scope.tickets_response_time.stats.push({
                title: this.$scope.tickets_response_time.titles[key]
              }));
            }
          }
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