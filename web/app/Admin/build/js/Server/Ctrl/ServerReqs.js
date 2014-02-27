(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_ServerReqs_Ctrl_ServerReqs;
    Admin_ServerReqs_Ctrl_ServerReqs = (function(_super) {
      __extends(Admin_ServerReqs_Ctrl_ServerReqs, _super);

      function Admin_ServerReqs_Ctrl_ServerReqs() {
        return Admin_ServerReqs_Ctrl_ServerReqs.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerReqs_Ctrl_ServerReqs.CTRL_ID = 'Admin_ServerReqs_Ctrl_ServerReqs';

      Admin_ServerReqs_Ctrl_ServerReqs.CTRL_AS = 'ServerReqs';

      Admin_ServerReqs_Ctrl_ServerReqs.DEPS = [];

      Admin_ServerReqs_Ctrl_ServerReqs.prototype.init = function() {
        return this.$scope.server_reqs = null;
      };

      Admin_ServerReqs_Ctrl_ServerReqs.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_reqs').then((function(_this) {
          return function(res) {
            return _this.$scope.server_reqs = res.data.server_reqs;
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      return Admin_ServerReqs_Ctrl_ServerReqs;

    })(Admin_Ctrl_Base);
    return Admin_ServerReqs_Ctrl_ServerReqs.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerReqs.js.map
