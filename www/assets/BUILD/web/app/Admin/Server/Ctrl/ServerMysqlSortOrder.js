define(['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) => {
  class Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder';
      this.CTRL_AS   = 'ServerMysqlSortOrder';
      this.DEPS      = [];
    }

    init() {
      this.$scope.server_mysql_sort_order = null;
      this.$scope.all_collations = null;
      this.$scope.current_sort_order = 'General Purpose (Default)';
      return this.$scope.update_started = false;
    }

    initialLoad() {
      const data_promise = this.Api.sendGet('/server_mysql_sort_order').then((res) => {
        this.$scope.server_mysql_sort_order = res.data.server_mysql_sort_order;
        this.$scope.all_collations = res.data.all_collations;

        if (this.$scope.all_collations[this.$scope.server_mysql_sort_order.db_collation] != null) {
          return this.$scope.current_sort_order = this.$scope.all_collations[this.$scope.server_mysql_sort_order.db_collation];
        }
      });

      return this.$q.all([data_promise]);
    }

    save() {
      if (!this.$scope.form_props.$valid) {
        return;
      }

      const postData = {
        server_mysql_sort_order: this.$scope.server_mysql_sort_order
      };

      this.startSpinner('saving');

      return this.Api.sendPostJson('/server_mysql_sort_order', postData).success(() => {
        this.server_mysql_sort_order = angular.copy(this.$scope.server_mysql_sort_order);

        return this.stopSpinner('saving').then(() => {
          this.$scope.current_sort_order = this.$scope.all_collations[this.$scope.server_mysql_sort_order.db_collation];
          this.$scope.update_started = true;

          return this.Growl.success('Update of sort order started');
        });
      }).error((info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });
    }
  }
  Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.initClass();

  return Admin_ServerMysqlSortOrder_Ctrl_ServerMysqlSortOrder.EXPORT_CTRL();
});
