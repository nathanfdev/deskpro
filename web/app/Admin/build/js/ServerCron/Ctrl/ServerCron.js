(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerCron_Ctrl_ServerCron, _ref;
    Admin_ServerCron_Ctrl_ServerCron = (function(_super) {
      __extends(Admin_ServerCron_Ctrl_ServerCron, _super);

      function Admin_ServerCron_Ctrl_ServerCron() {
        _ref = Admin_ServerCron_Ctrl_ServerCron.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_ServerCron_Ctrl_ServerCron.CTRL_ID = 'Admin_ServerCron_Ctrl_ServerCron';

      Admin_ServerCron_Ctrl_ServerCron.CTRL_AS = 'ServerCron';

      Admin_ServerCron_Ctrl_ServerCron.DEPS = [];

      Admin_ServerCron_Ctrl_ServerCron.prototype.init = function() {
        return this.$scope.cron = null;
      };

      Admin_ServerCron_Ctrl_ServerCron.prototype.initialLoad = function() {
        var data_promise,
          _this = this;
        data_promise = this.Api.sendGet('/server_cron').then(function(res) {
          return _this.$scope.server_cron = res.data.server_cron;
        });
        return this.$q.all([data_promise]);
      };

      return Admin_ServerCron_Ctrl_ServerCron;

    })(Admin_Ctrl_Base);
    return Admin_ServerCron_Ctrl_ServerCron.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=ServerCron.js.map
*/