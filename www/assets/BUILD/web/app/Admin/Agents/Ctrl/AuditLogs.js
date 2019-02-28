define(['Admin/Main/Ctrl/Base', '../../../../bower_components/moment/moment'], function(Admin_Ctrl_Base, moment) {
  class Admin_AgentAuditLogs_Ctrl_AuditLogs extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_AgentAuditLogs_Ctrl_AuditLogs';
      this.CTRL_AS   = 'AuditLogs';
      this.DEPS      = ['Api2'];
    }

    init() {
      this.filters = {
        performer_id:      '',
        performer_name:    '',
        date_created_to:   null,
        date_created_from: null,
        object_type:       '',
        object_id:         '',
        object_name:       '',
        action:            '',
        api_key:           ''
      };
      this.logs = [];
      this.purge = 'day';
      return this.pagination = {
        total:                0,
        count:                0,
        per_page:             50,
        current_page:         1,
        virtual_current_page: 1,
        total_pages:          1,
        page_nums:            [1],
        plain:                false
      };
    }

    initialLoad() {
      return this.updateFilter();
    }

    newSearch() {
      this.pagination.current_page = (this.pagination.virtual_current_page = 1);
      return this.updateFilter();
    }

    updateFilter() {
      this.is_loading = true;
      const params = {};
      for (const key of Array.from(Object.keys(this.filters))) {
        if (this.filters[key]) { params[key] = this.filters[key]; }
      }
      params.count = this.pagination.per_page;
      params.page = this.pagination.current_page;

      if (params.date_created_from) {
        params.date_created_from = moment(params.date_created_from).format('YYYY-MM-DD');
      }

      if (params.date_created_to) {
        params.date_created_to = moment(params.date_created_to).format('YYYY-MM-DD');
      }

      return this.Api2.sendGet('/audit_logs', params).then(
        (response) => {
          this.logs = response.data.data;
          this.pagination = response.data.meta.pagination;
          this.pagination.virtual_current_page = this.pagination.current_page;
          const page_nums = [];
          if (this.pagination.total_pages > 250) {
            this.pagination.plain = true;
            this.is_loading = false;
            return;
          }

          for (let i = 0, end = this.pagination.total_pages, asc = end >= 0; asc ? i < end : i > end; asc ? i++ : i--) {
            page_nums.push(i + 1);
          }
          this.pagination.page_nums = page_nums;
          return this.is_loading = false;
        });
    }

    purgeLogs() {
      this.is_loading = true;
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Agents/audit-logs-delete-modal.html'),
        controller:  ['$scope', '$modalInstance',  function ($scope, $modalInstance) {
          $scope.confirm = () => $modalInstance.close();

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst.result.then(() => this.Api2.sendPostJson('/audit_logs/purge', { period: this.purge }).then(
          () => {
            this.clearFilter();
            return this.is_loading = false;
          })).catch((() => this.is_loading = false));
    }


    goPrevPage() {
      this.pagination.current_page = (this.pagination.virtual_current_page = parseInt(this.pagination.current_page) - 1);
      if (this.pagination.current_page < 0) {
        this.pagination.current_page = (this.pagination.virtual_current_page = 0);
      }
      return this.updateFilter();
    }

    goNextPage() {
      this.pagination.current_page = (this.pagination.virtual_current_page = parseInt(this.pagination.current_page) + 1);
      if (this.pagination.current_page > this.pagination.total_pages) {
        this.pagination.current_page = (this.pagination.virtual_current_page = this.pagination.total_pages);
      }
      return this.updateFilter();
    }

    goCurrentPage() {
      this.pagination.current_page = parseInt(this.pagination.virtual_current_page);
      if (this.pagination.current_page > this.pagination.total_pages) {
        this.pagination.current_page = (this.pagination.virtual_current_page = this.pagination.total_pages);
      } else if (this.pagination.current_page < 0) {
        this.pagination.current_page = (this.pagination.virtual_current_page = 0);
      }
      this.updateFilter();
      return false;
    }

    clearFilter() {
      this.filters = {
        performer_id:      '',
        performer_name:    '',
        date_created_to:   null,
        date_created_from: null,
        object_type:       '',
        object_id:         '',
        object_name:       '',
        action:            '',
        api_key:           ''
      };
      this.pagination.current_page = 1;
      this.pagination.virtual_current_page = 1;
      return this.updateFilter();
    }
  }
  Admin_AgentAuditLogs_Ctrl_AuditLogs.initClass();

  return Admin_AgentAuditLogs_Ctrl_AuditLogs.EXPORT_CTRL();
});
