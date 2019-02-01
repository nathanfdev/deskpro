// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
  'moment'
], function(
  BaseListEdit,
  moment
)  {
  class Admin_ApiKeys_DataService_ApiLogs extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api2', '$q'];
    }

    init() {
      this.filtration = {
        mode: '',
        method: ''
      };
      return this.pagination = {
        page: 1
      };
    }

    getFiltration() {
      return this.filtration;
    }

    getOptions() {
      const deferred = this.$q.defer();
      this.Api2.sendGet('/api_logs_options')
      .success(data => {
        return deferred.resolve(data.data);
      })
      .error( () => deferred.reject() );

      return deferred.promise;
    }

    updateOptions(options){
      const deferred = this.$q.defer();
      return this.Api2.sendPutJson('/api_logs_options', options)
      .success(data => {
        return deferred.resolve(data.data);
      })
      .error( () => deferred.reject() );
    }

    _doLoadList() {
      const deferred = this.$q.defer();

      this.Api2.sendGet(`/api_logs?page=${this.pagination.page}`)
      .success(data => {
        const models = this.mutateData(data);
        return deferred.resolve(models);
      })
      .error( () => deferred.reject() );

      return deferred.promise;
    }

    _doRefreshList() {
      const deferred = this.$q.defer();
      const pagination = this.getPagination();
      this.Api2.sendGet('/api_logs', {
        page: pagination.page
      }).success(data => {
          const models = this.mutateData(data);
          return deferred.resolve(models);
        }
      , () => deferred.reject());

      return deferred.promise;
    }

    mutateData(data) {
      const models = [];
      for (let model of Array.from(data.data)) {
        model.start_date_time = moment.unix(model.start_time).format('YYYY-MM-DD[\u00A0]H:mm:ss');
        models.push(model);
      }
      models.pagination = {
        total: data.meta.pagination.total,
        num_pages: data.meta.pagination.total_pages - 1,
        page: data.meta.pagination.current_page
      };

      return models;
    }

    loadLog(id) {
      const deferred = this.$q.defer();

      this.Api2.sendGet(`/api_logs/${id}?include=data`).success(data => {
        const model = data.data;
        model.start_date_time = moment.unix(model.start_time).format('YYYY-MM-DD[\u00A0]H:mm:ss');
        return deferred.resolve(data.data);
      }
      , () => deferred.reject());

      return deferred.promise;
    }

    replay(model, mode) {
      const deferred = this.$q.defer();
      this.Api2.sendPostJson(`/api_logs/${model.id}/replay`, {mode, request_id: model.request_id})
      .success(data => {
        return deferred.resolve(data.data);
      });

      return deferred.promise;
    }
  }
  Admin_ApiKeys_DataService_ApiLogs.initClass();
  return Admin_ApiKeys_DataService_ApiLogs;
});