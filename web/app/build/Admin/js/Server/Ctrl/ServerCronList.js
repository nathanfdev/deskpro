(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerCron_Ctrl_List;
    Admin_ServerCron_Ctrl_List = (function(_super) {
      __extends(Admin_ServerCron_Ctrl_List, _super);

      function Admin_ServerCron_Ctrl_List() {
        return Admin_ServerCron_Ctrl_List.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerCron_Ctrl_List.CTRL_ID = 'Admin_ServerCron_Ctrl_List';

      Admin_ServerCron_Ctrl_List.CTRL_AS = 'ListCtrl';

      Admin_ServerCron_Ctrl_List.DEPS = [];

      Admin_ServerCron_Ctrl_List.prototype.init = function() {
        return this.server_cron = null;
      };

      Admin_ServerCron_Ctrl_List.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_cron').then((function(_this) {
          return function(res) {
            return _this.server_cron = res.data.server_cron;
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      return Admin_ServerCron_Ctrl_List;

    })(Admin_Ctrl_Base);
    return Admin_ServerCron_Ctrl_List.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerCronList.js.map
