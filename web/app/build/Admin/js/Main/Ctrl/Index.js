(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_Index;
    Admin_Main_Ctrl_Index = (function(_super) {
      __extends(Admin_Main_Ctrl_Index, _super);

      function Admin_Main_Ctrl_Index() {
        return Admin_Main_Ctrl_Index.__super__.constructor.apply(this, arguments);
      }

      Admin_Main_Ctrl_Index.CTRL_ID = 'Admin_Main_Ctrl_Index';

      Admin_Main_Ctrl_Index.prototype.init = function() {};

      return Admin_Main_Ctrl_Index;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_Index.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Index.js.map
