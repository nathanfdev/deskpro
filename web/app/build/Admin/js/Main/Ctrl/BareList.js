(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Main_Ctrl_BareList;
    Admin_Main_Ctrl_BareList = (function(_super) {
      __extends(Admin_Main_Ctrl_BareList, _super);

      function Admin_Main_Ctrl_BareList() {
        return Admin_Main_Ctrl_BareList.__super__.constructor.apply(this, arguments);
      }

      Admin_Main_Ctrl_BareList.CTRL_ID = 'Admin_Main_Ctrl_BareList';

      return Admin_Main_Ctrl_BareList;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_BareList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=BareList.js.map
