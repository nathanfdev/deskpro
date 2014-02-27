(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder;
    Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder = (function(_super) {
      __extends(Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder, _super);

      function Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder() {
        return Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.__super__.constructor.apply(this, arguments);
      }

      Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.CTRL_ID = 'Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder';

      Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.CTRL_AS = 'ServerMysqlSortOrder';

      Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.DEPS = [];

      Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.prototype.init = function() {
        this.$scope.server_mysql_sort_order = null;
        this.$scope.all_collations = null;
        this.$scope.current_sort_order = 'General Purpose (Default)';
        return this.$scope.update_started = false;
      };

      Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendGet('/server_mysql_sort_order').then((function(_this) {
          return function(res) {
            _this.$scope.server_mysql_sort_order = res.data.server_mysql_sort_order;
            _this.$scope.all_collations = res.data.all_collations;
            if (_this.$scope.all_collations[_this.$scope.server_mysql_sort_order.db_collation] != null) {
              return _this.$scope.current_sort_order = _this.$scope.all_collations[_this.$scope.server_mysql_sort_order.db_collation];
            }
          };
        })(this));
        return this.$q.all([data_promise]);
      };

      Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.prototype.save = function() {
        var postData;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        postData = {
          server_mysql_sort_order: this.$scope.server_mysql_sort_order
        };
        this.startSpinner('saving');
        return this.Api.sendPostJson('/server_mysql_sort_order', postData).success((function(_this) {
          return function() {
            _this.server_mysql_sort_order = angular.copy(_this.$scope.server_mysql_sort_order);
            return _this.stopSpinner('saving').then(function() {
              _this.$scope.current_sort_order = _this.$scope.all_collations[_this.$scope.server_mysql_sort_order.db_collation];
              _this.$scope.update_started = true;
              return _this.Growl.success('Update of sort order started');
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder;

    })(Admin_Ctrl_Base);
    return Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=ServerMysqlSortOrder.js.map
