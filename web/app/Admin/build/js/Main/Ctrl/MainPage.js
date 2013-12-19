(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_MainPage, _ref;
    Admin_Main_Ctrl_MainPage = (function(_super) {
      __extends(Admin_Main_Ctrl_MainPage, _super);

      function Admin_Main_Ctrl_MainPage() {
        _ref = Admin_Main_Ctrl_MainPage.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Main_Ctrl_MainPage.CTRL_ID = 'Admin_Main_Ctrl_MainPage';

      Admin_Main_Ctrl_MainPage.DEPS = ['$rootScope', 'AppState'];

      Admin_Main_Ctrl_MainPage.prototype.init = function() {};

      return Admin_Main_Ctrl_MainPage;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_MainPage.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=MainPage.js.map
*/