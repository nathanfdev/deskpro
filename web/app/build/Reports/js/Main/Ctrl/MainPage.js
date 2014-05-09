(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Reports/Main/Ctrl/Base'], function(ReportsBaseCtrl) {
    var Reports_Main_Ctrl_MainPage;
    Reports_Main_Ctrl_MainPage = (function(_super) {
      __extends(Reports_Main_Ctrl_MainPage, _super);

      function Reports_Main_Ctrl_MainPage() {
        return Reports_Main_Ctrl_MainPage.__super__.constructor.apply(this, arguments);
      }

      Reports_Main_Ctrl_MainPage.CTRL_ID = 'Reports_Main_Ctrl_MainPage';

      Reports_Main_Ctrl_MainPage.DEPS = ['$rootScope', 'AppState'];

      Reports_Main_Ctrl_MainPage.prototype.init = function() {};

      return Reports_Main_Ctrl_MainPage;

    })(ReportsBaseCtrl);
    return Reports_Main_Ctrl_MainPage.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=MainPage.js.map
