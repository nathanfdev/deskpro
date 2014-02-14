(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(ReportsBaseCtrl, Util) {
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
      		#
      */


      Reports_AgentActivity_Ctrl_AgentActivity.prototype.init = function() {
        this.html = '';
      };

      /*
      		# Just doing all the necessary AJAX calls here
      */


      Reports_AgentActivity_Ctrl_AgentActivity.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendGet("/reports/agent-activity").then(function(res) {
          return _this.html = _this.$sce.trustAsHtml(res.data.html);
        });
        return this.$q.all([data_promise]);
      };

      return Reports_AgentActivity_Ctrl_AgentActivity;

    })(ReportsBaseCtrl);
    return Reports_AgentActivity_Ctrl_AgentActivity.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=AgentActivity.js.map
*/