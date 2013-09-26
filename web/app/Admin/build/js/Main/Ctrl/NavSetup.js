(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_NavSetup, _ref;
    Admin_Main_Ctrl_NavSetup = (function(_super) {
      __extends(Admin_Main_Ctrl_NavSetup, _super);

      function Admin_Main_Ctrl_NavSetup() {
        _ref = Admin_Main_Ctrl_NavSetup.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Main_Ctrl_NavSetup.CTRL_ID = 'Admin_Main_Ctrl_NavSetup';

      Admin_Main_Ctrl_NavSetup.prototype.init = function() {};

      return Admin_Main_Ctrl_NavSetup;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_NavSetup.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=NavSetup.js.map
*/