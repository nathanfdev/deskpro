(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'moment'], function(ReportsBaseCtrl, moment) {
    var Reports_AgentHours_Ctrl_AgentHours;
    Reports_AgentHours_Ctrl_AgentHours = (function(_super) {
      __extends(Reports_AgentHours_Ctrl_AgentHours, _super);

      function Reports_AgentHours_Ctrl_AgentHours() {
        return Reports_AgentHours_Ctrl_AgentHours.__super__.constructor.apply(this, arguments);
      }

      Reports_AgentHours_Ctrl_AgentHours.CTRL_ID = 'Reports_AgentHours_Ctrl_AgentHours';

      Reports_AgentHours_Ctrl_AgentHours.CTRL_AS = 'AgentHours';

      Reports_AgentHours_Ctrl_AgentHours.DEPS = ['Api', '$sce'];


      /*
      		 * Initializing..
       */

      Reports_AgentHours_Ctrl_AgentHours.prototype.init = function() {
        this.html = '';
        this.date1 = new Date();
        this.date2 = new Date();
        this.filter = {};
        this.filter.date1 = moment(this.date).format("YYYY-MM-DD");
        return this.filter.date2 = moment(this.date).format("YYYY-MM-DD");
      };


      /*
      		 * Just doing all the necessary AJAX calls here
       */

      Reports_AgentHours_Ctrl_AgentHours.prototype.initialLoad = function() {
        return this.loadResults();
      };


      /*
      		 * This method updates current parameters that are used for sending request to API
       */

      Reports_AgentHours_Ctrl_AgentHours.prototype.updateFilter = function() {
        this.filter.date1 = moment(this.date1).format("YYYY-MM-DD");
        this.filter.date2 = moment(this.date2).format("YYYY-MM-DD");
        return this.loadResults();
      };


      /*
      		 * Loading the results of sending request to API
       */

      Reports_AgentHours_Ctrl_AgentHours.prototype.loadResults = function() {
        var promise;
        this.startSpinner('loading_results');
        promise = this.Api.sendGet("/reports/agent-hours/" + this.filter.date1 + "/" + this.filter.date2).then((function(_this) {
          return function(res) {
            _this.html = _this.$sce.trustAsHtml(res.data.html);
            return _this.stopSpinner('loading_results', true);
          };
        })(this));
        return promise;
      };

      return Reports_AgentHours_Ctrl_AgentHours;

    })(ReportsBaseCtrl);
    return Reports_AgentHours_Ctrl_AgentHours.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=AgentHours.js.map
