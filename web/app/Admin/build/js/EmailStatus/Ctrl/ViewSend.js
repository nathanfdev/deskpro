(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'moment'], function(Admin_Ctrl_Base, moment) {
    var Admin_EmailStatus_Ctrl_ViewSend;
    Admin_EmailStatus_Ctrl_ViewSend = (function(_super) {
      __extends(Admin_EmailStatus_Ctrl_ViewSend, _super);

      function Admin_EmailStatus_Ctrl_ViewSend() {
        return Admin_EmailStatus_Ctrl_ViewSend.__super__.constructor.apply(this, arguments);
      }

      Admin_EmailStatus_Ctrl_ViewSend.CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSend';

      Admin_EmailStatus_Ctrl_ViewSend.CTRL_AS = 'List';

      Admin_EmailStatus_Ctrl_ViewSend.DEPS = [];

      Admin_EmailStatus_Ctrl_ViewSend.prototype.init = function() {};

      return Admin_EmailStatus_Ctrl_ViewSend;

    })(Admin_Ctrl_Base);
    return Admin_EmailStatus_Ctrl_ViewSend.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ViewSend.js.map
