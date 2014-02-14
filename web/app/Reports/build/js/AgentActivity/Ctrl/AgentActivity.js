(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'moment'], function(ReportsBaseCtrl, moment) {
    var Reports_AgentActivity_Ctrl_AgentActivity, _ref;
    Reports_AgentActivity_Ctrl_AgentActivity = (function(_super) {
      __extends(Reports_AgentActivity_Ctrl_AgentActivity, _super);

      function Reports_AgentActivity_Ctrl_AgentActivity() {
        _ref = Reports_AgentActivity_Ctrl_AgentActivity.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Reports_AgentActivity_Ctrl_AgentActivity.CTRL_ID = 'Reports_AgentActivity_Ctrl_AgentActivity';

      Reports_AgentActivity_Ctrl_AgentActivity.CTRL_AS = 'AgentActivity';

      Reports_AgentActivity_Ctrl_AgentActivity.DEPS = ['Api', '$sce'];

      /*
      		# Initializing..
      */


      Reports_AgentActivity_Ctrl_AgentActivity.prototype.init = function() {
        this.html = '';
        this.date = new Date();
        return this.filter = {};
      };

      /*
      		# Just doing all the necessary AJAX calls here
      */


      Reports_AgentActivity_Ctrl_AgentActivity.prototype.initialLoad = function() {
        return this.loadResults();
      };

      /*
       	# This method updates current parameters that are used for sending request to API
      */


      Reports_AgentActivity_Ctrl_AgentActivity.prototype.updateFilter = function() {
        console.log(this.date);
        this.filter.date = moment(this.date).format("YYYY-MM-DD");
        return this.loadResults();
      };

      /*
       	# Loading the results of sending request to API
      */


      Reports_AgentActivity_Ctrl_AgentActivity.prototype.loadResults = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet("/reports/agent-activity/0/" + this.filter.date).then(function(res) {
          return _this.html = _this.$sce.trustAsHtml(res.data.html);
        });
        return promise;
      };

      return Reports_AgentActivity_Ctrl_AgentActivity;

    })(ReportsBaseCtrl);
    return Reports_AgentActivity_Ctrl_AgentActivity.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=AgentActivity.js.map
*/