(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_AuditLog_Ctrl_List;
    Admin_AuditLog_Ctrl_List = (function(_super) {
      __extends(Admin_AuditLog_Ctrl_List, _super);

      function Admin_AuditLog_Ctrl_List() {
        return Admin_AuditLog_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_AuditLog_Ctrl_List.CTRL_ID = 'Admin_AuditLog_Ctrl_List';

      Admin_AuditLog_Ctrl_List.CTRL_AS = 'List';

      Admin_AuditLog_Ctrl_List.DEPS = [];

      Admin_AuditLog_Ctrl_List.prototype.init = function() {};

      Admin_AuditLog_Ctrl_List.prototype.initialLoad = function() {
        return this.Api.sendGet('/audit_log').then((function(_this) {
          return function(res) {
            _this.logs = res.data.logs;
            _this.total = res.data.total;
            return _this.num_pages = res.data.num_pages;
          };
        })(this));
      };

      return Admin_AuditLog_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_AuditLog_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=List.js.map
