// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit'
], function(
  BaseListEdit
)  {
  class Admin_ApiKeys_DataService_ApiKeys extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    url() { return '/api_keys'; }

    init() {
      this.limits = {
        daily_limit: -1,
        hourly_limit: -1
      };

      return this.Api.sendGet('/api_keys_settings')
        .success(data => {
          return this.limits = data;
        });
    }

    replayLogEntry(entry) {
      const deferred = this.$q.defer();

      this.Api.sendGet(`/api_keys/replay/${entry.id}`)
      .success((data, status, headers, config) => {
        this.loadList(true);
        return deferred.resolve(data, status);
      })
      .error((data, status, headers, config) => deferred.reject(data, status));

      return deferred.promise;
    }

    getLogs(entry) {
      const deferred = this.$q.defer();

      this.Api.sendGet(`/api_keys/${entry.id}/logs`).success((data, status, headers, config) => {
        return deferred.resolve(data, status);
      }).error((data, status, headers, config) => deferred.reject(data, status));

      return deferred.promise;
    }

    getSettings() {
      let deferred;
      return deferred = this.$q.defer;
    }

    /*
     * Generate new API key code
     *
     * @param {Object} model api_key model
     * @return {promise}
     */
    regenerateApiKey(model) {
      return this.Api.sendPostJson(`/api_keys/regenerate/${model.id}`).success(data => {
        model.code = data.code;
        return model.keyString = data.keyString;
      });
    }
  }
  Admin_ApiKeys_DataService_ApiKeys.initClass();
  return Admin_ApiKeys_DataService_ApiKeys;
});