(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(ReportsBaseCtrl, Util) {
    var Reports_Overview_Ctrl_Overview;
    Reports_Overview_Ctrl_Overview = (function(_super) {
      __extends(Reports_Overview_Ctrl_Overview, _super);

      function Reports_Overview_Ctrl_Overview() {
        return Reports_Overview_Ctrl_Overview.__super__.constructor.apply(this, arguments);
      }

      Reports_Overview_Ctrl_Overview.CTRL_ID = 'Reports_Overview_Ctrl_Overview';

      Reports_Overview_Ctrl_Overview.CTRL_AS = 'Overview';

      Reports_Overview_Ctrl_Overview.DEPS = ['Api'];


      /*
      		 *
       */

      Reports_Overview_Ctrl_Overview.prototype.init = function() {};


      /*
      		 * Just doing all the necessary AJAX calls here
       */

      Reports_Overview_Ctrl_Overview.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          tickets_status: "/reports/overview/data/tickets_status",
          tickets_awaiting_agent: "/reports/overview/data/tickets_awaiting_agent",
          tickets_user_waiting_time: "/reports/overview/data/tickets_user_waiting_time",
          tickets_resolved: "/reports/overview/data/tickets_resolved",
          tickets_response_time: "/reports/overview/data/tickets_response_time",
          tickets_sla_status: "/reports/overview/data/tickets_sla_status",
          tickets_opened_hour: "/reports/overview/data/tickets_opened_hour",
          chats_created: "/reports/overview/data/chats_created"
        }).then((function(_this) {
          return function(res) {
            _this.$scope.tickets_status = res.data.tickets_status;
            _this.$scope.tickets_awaiting_agent = res.data.tickets_awaiting_agent;
            _this.$scope.tickets_user_waiting_time = res.data.tickets_user_waiting_time;
            _this.$scope.tickets_resolved = res.data.tickets_resolved;
            _this.$scope.tickets_response_time = res.data.tickets_response_time;
            _this.$scope.tickets_sla_status = res.data.tickets_sla_status;
            _this.$scope.tickets_opened_hour = res.data.tickets_opened_hour;
            _this.$scope.chats_created = res.data.chats_created;
            _this.setDataForBarGraphs('tickets_status');
            _this.setDataForBarGraphs('tickets_awaiting_agent');
            _this.setDataForBarGraphs('tickets_resolved');
            _this.setDataForBarGraphs('tickets_sla_status');
            _this.setDataForBarGraphs('chats_created');
            _this.setDataForTicketsOpenedHours();
            _this.setDataForTableWithBarGraphs('tickets_user_waiting_time');
            return _this.setDataForTableWithBarGraphs('tickets_response_time');
          };
        })(this));
        return this.$q.all([data_promise]);
      };


      /*
       	 * This method is used in select boxes for defining grouping field and / or other search parameters
       	 * @param {String} data_key - using this key data is looked in @$scope
       */

      Reports_Overview_Ctrl_Overview.prototype.getStats = function(data_key) {
        var promise;
        this.toggleLoadingState(data_key);
        promise = this.Api.sendGet('/reports/overview/get-stats/' + data_key, {
          grouping_field: this.$scope[data_key].grouping_field,
          date_choice: this.$scope[data_key].date_choice,
          sla_id: this.$scope[data_key].sla_id
        });
        return promise.success((function(_this) {
          return function(data) {
            _this.toggleLoadingState(data_key);
            _this.$scope[data_key] = data;
            if (data_key === 'tickets_user_waiting_time' || data_key === 'tickets_response_time') {
              return _this.setDataForTableWithBarGraphs(data_key);
            } else if (data_key === 'tickets_opened_hour') {
              return _this.setDataForTicketsOpenedHours();
            } else {
              return _this.setDataForBarGraphs(data_key);
            }
          };
        })(this));
      };


      /*
       	 * Used for hiding / showing AJAX loader
       */

      Reports_Overview_Ctrl_Overview.prototype.toggleLoadingState = function(data_key) {
        return this.$scope[data_key].loading = !this.$scope[data_key].loading;
      };


      /*
      		 * We need to display bar graphs - so let's pre-calculate some variables
       	 * @param {String} data_key - using this key data is looked in @$scope
       */

      Reports_Overview_Ctrl_Overview.prototype.setDataForBarGraphs = function(data_key) {
        var denominator, key, percentage, _results;
        if (Util.isEmpty(this.$scope[data_key].values)) {
          this.$scope[data_key].empty = true;
        }
        this.$scope[data_key].stats = [];
        denominator = this.$scope[data_key].max || 1;
        _results = [];
        for (key in this.$scope[data_key].titles) {
          if (!this.$scope[data_key].values[key]) {
            continue;
          }
          percentage = this.$scope[data_key].values[key] / denominator * 100;
          if (percentage < 1) {
            percentage = 1;
          }
          _results.push(this.$scope[data_key].stats.push({
            title: this.$scope[data_key].titles[key],
            value: this.$scope[data_key].values[key] || 0,
            left_percentage: percentage,
            right_percentage: 100 - percentage
          }));
        }
        return _results;
      };


      /*
      		 * We need to display bar graphs - so let's pre-calculate some variables
       	 * This method is special case of @setDataForBarGraphs()
       */

      Reports_Overview_Ctrl_Overview.prototype.setDataForTicketsOpenedHours = function() {
        var denominator, key, percentage, _results;
        if (Util.isEmpty(this.$scope.tickets_opened_hour.values)) {
          this.$scope.tickets_opened_hour.empty = true;
        }
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
      		 * We need to display bar graphs - so let's pre-calculate some variables
      		 * What is special here - we display every piece of data
      		 * Just for cases with no data we display only labels without graphical bars
      		 * Ie. if we have 0 tickets created < 5 minutes ago, we still display '< 5 minutes' label, but without bar
      		 * This leads to the situation that we have to iterate over all the '@$scope.tickets_user_waiting_time.titles' array
       	 * @param {String} data_key - using this key data is looked in @$scope
       */

      Reports_Overview_Ctrl_Overview.prototype.setDataForTableWithBarGraphs = function(data_key) {
        var denominator, key, percentage, sub_percentage, sub_stats, subid, subtitle, _ref, _results;
        if (Util.isEmpty(this.$scope[data_key].values)) {
          this.$scope[data_key].empty = true;
        }
        this.$scope[data_key].stats = [];
        denominator = this.$scope[data_key].max || 1;
        _results = [];
        for (key in this.$scope[data_key].titles) {
          percentage = this.$scope[data_key].values[key] / denominator * 100;
          if (percentage < 1) {
            percentage = 1;
          }
          if (!this.$scope[data_key].sub_titles) {
            if (this.$scope[data_key].values[key]) {
              _results.push(this.$scope[data_key].stats.push({
                title: this.$scope[data_key].titles[key],
                value: this.$scope[data_key].values[key] || 0,
                percentage: percentage
              }));
            } else {
              _results.push(this.$scope[data_key].stats.push({
                title: this.$scope[data_key].titles[key]
              }));
            }
          } else {
            percentage = this.$scope[data_key].group_total[key] / denominator * 100;
            if (percentage < 1) {
              percentage = 1;
            }
            if (this.$scope[data_key].group_total[key]) {
              sub_stats = [];
              _ref = this.$scope[data_key].sub_titles;
              for (subid in _ref) {
                subtitle = _ref[subid];
                if (!this.$scope[data_key].values[key][subid]) {
                  continue;
                }
                sub_percentage = this.$scope[data_key].values[key][subid] / this.$scope[data_key].group_total[key] * 100;
                if (sub_percentage < 1) {
                  sub_percentage = 1;
                }
                sub_stats.push({
                  title: subtitle + ' (' + this.$scope[data_key].values[key][subid] + ')',
                  percentage: sub_percentage,
                  background: this.$scope[data_key].group_keys[subid]
                });
              }
              _results.push(this.$scope[data_key].stats.push({
                title: this.$scope[data_key].titles[key],
                value: this.$scope[data_key].group_total[key] || 0,
                percentage: percentage,
                sub_stats: sub_stats
              }));
            } else {
              _results.push(this.$scope[data_key].stats.push({
                title: this.$scope[data_key].titles[key]
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

//# sourceMappingURL=Overview.js.map
