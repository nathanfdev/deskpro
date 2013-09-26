(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/App'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_NavBase, _ref;
    Admin_Main_Ctrl_NavBase = (function(_super) {
      __extends(Admin_Main_Ctrl_NavBase, _super);

      function Admin_Main_Ctrl_NavBase() {
        _ref = Admin_Main_Ctrl_NavBase.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Main_Ctrl_NavBase.CTRL_ID = 'Admin_Main_Ctrl_NavBase';

      Admin_Main_Ctrl_NavBase.prototype.init = function() {};

      return Admin_Main_Ctrl_NavBase;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_NavBase.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=NavBase.js.map
*/