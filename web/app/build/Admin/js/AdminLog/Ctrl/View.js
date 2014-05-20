(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_AdminLog_Ctrl_View;
    Admin_AdminLog_Ctrl_View = (function(_super) {
      __extends(Admin_AdminLog_Ctrl_View, _super);

      function Admin_AdminLog_Ctrl_View() {
        return Admin_AdminLog_Ctrl_View.__super__.constructor.apply(this, arguments);
      }

      Admin_AdminLog_Ctrl_View.CTRL_ID = 'Admin_AdminLog_Ctrl_View';

      Admin_AdminLog_Ctrl_View.CTRL_AS = 'List';

      Admin_AdminLog_Ctrl_View.DEPS = [];

      Admin_AdminLog_Ctrl_View.prototype.init = function() {
        this.logId = parseInt(this.$stateParams.id);
      };

      Admin_AdminLog_Ctrl_View.prototype.initialLoad = function() {};

      return Admin_AdminLog_Ctrl_View;

    })(Admin_Ctrl_Base);
    return Admin_AdminLog_Ctrl_View.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=View.js.map
