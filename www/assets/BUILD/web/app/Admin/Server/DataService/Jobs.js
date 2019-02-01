define([
  'Admin/Main/DataService/BaseListEdit',
  'moment'
], (
  BaseListEdit,
  moment
) => {
  class Admin_Server_DataService_Jobs extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api2', '$q'];
    }

    init() {
      return this.pagination = {
        page: 1
      };
    }

    _doLoadList() {
      const deferred = this.$q.defer();

      this.Api2.sendGet(`/jobs?page=${this.pagination.page}`)
      .success((data) => {
        const models = this.mutateData(data);
        return deferred.resolve(models);
      })
      .error(() => deferred.reject());

      return deferred.promise;
    }

    _doRefreshList() {
      const deferred = this.$q.defer();
      const pagination = this.getPagination();
      this.Api2.sendGet('/jobs', {
        page: pagination.page
      }).success((data) => {
        const models = this.mutateData(data);
        return deferred.resolve(models);
      }
      , () => deferred.reject());

      return deferred.promise;
    }

    mutateData(data) {
      const models = [];
      for (const model of Array.from(data.data)) {
        models.push(model);
      }
      models.pagination = {
        total:     data.meta.pagination.total,
        num_pages: data.meta.pagination.total_pages - 1,
        page:      data.meta.pagination.current_page
      };

      return models;
    }

    loadJob(id) {
      const deferred = this.$q.defer();

      this.Api2.sendGet(`/jobs/${id}`).success(data => deferred.resolve(data.data)
      , () => deferred.reject());

      return deferred.promise;
    }
  }
  Admin_Server_DataService_Jobs.initClass();
  return Admin_Server_DataService_Jobs;
});
