(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_Bare;
    Admin_Main_Ctrl_Bare = (function(_super) {
      __extends(Admin_Main_Ctrl_Bare, _super);

      function Admin_Main_Ctrl_Bare() {
        return Admin_Main_Ctrl_Bare.__super__.constructor.apply(this, arguments);
      }

      Admin_Main_Ctrl_Bare.CTRL_ID = 'Admin_Main_Ctrl_Bare';

      return Admin_Main_Ctrl_Bare;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_Bare.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Bare.js.map
