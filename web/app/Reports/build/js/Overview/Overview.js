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

      Reports_Overview_Ctrl_Overview.prototype.init = function() {};

      return Reports_Overview_Ctrl_Overview;

    })(ReportsBaseCtrl);
    return Reports_Overview_Ctrl_Overview.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Overview.js.map
*/