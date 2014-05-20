(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ServerTaskQueue_Ctrl_ServerTaskQueue;
    Admin_ServerTaskQueue_Ctrl_ServerTaskQueue = (function(_super) {
      __extends(Admin_ServerTaskQueue_Ctrl_ServerTaskQueue, _super);

      function Admin_ServerTaskQueue_Ctrl_ServerTaskQueue() {
        return Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.CTRL_ID = 'Admin_ServerTaskQueue_Ctrl_ServerTaskQueue';

      Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.CTRL_AS = 'ServerTaskQueue';

      Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.DEPS = [];

      Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.prototype.init = function() {
        return this.$scope.server_task_queue = null;
      };

      Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_task_queue').then((function(_this) {
          return function(res) {
            return _this.$scope.server_task_queue = res.data.server_task_queue;
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      return Admin_ServerTaskQueue_Ctrl_ServerTaskQueue;

    })(Admin_Ctrl_Base);
    return Admin_ServerTaskQueue_Ctrl_ServerTaskQueue.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerTaskQueue.js.map
