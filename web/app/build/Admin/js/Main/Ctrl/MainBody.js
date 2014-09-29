(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_MainBody;
    Admin_Main_Ctrl_MainBody = (function(_super) {
      __extends(Admin_Main_Ctrl_MainBody, _super);

      function Admin_Main_Ctrl_MainBody() {
        return Admin_Main_Ctrl_MainBody.__super__.constructor.apply(this, arguments);
      }

      Admin_Main_Ctrl_MainBody.CTRL_ID = 'Admin_Main_Ctrl_MainBody';

      Admin_Main_Ctrl_MainBody.DEPS = [];

      return Admin_Main_Ctrl_MainBody;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_MainBody.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=MainBody.js.map
