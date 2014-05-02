(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['AdminStart/Ctrl/StartBase'], function(StartBase) {
    var AdminStart_Ctrl_Cron;
    AdminStart_Ctrl_Cron = (function(_super) {
      __extends(AdminStart_Ctrl_Cron, _super);

      function AdminStart_Ctrl_Cron() {
        return AdminStart_Ctrl_Cron.__super__.constructor.apply(this, arguments);
      }

      AdminStart_Ctrl_Cron.CTRL_ID = 'AdminStart_Ctrl_Cron';

      AdminStart_Ctrl_Cron.prototype.init = function() {};

      return AdminStart_Ctrl_Cron;

    })(StartBase);
    return AdminStart_Ctrl_Cron.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Cron.js.map
