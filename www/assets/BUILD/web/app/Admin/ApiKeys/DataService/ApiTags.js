define([
  'Admin/Main/DataService/BaseListEdit'
], (
  BaseListEdit
) => {
  class Admin_ApiKeys_DataService_ApiTags extends BaseListEdit {
    static initClass() {
      this.$inject = ['Api2', '$q'];
    }

    getTags(id) {
      const deferred = this.$q.defer();
      this.Api2.sendGet(`/api_tags/${id}/flatten`)
      .success(data => deferred.resolve(data.data))
      .error((data, status, headers, config) => deferred.reject());

      return deferred.promise;
    }
    updateTags(tags, id) {
      const deferred = this.$q.defer();
      return this.Api2.sendPutJson(`/api_tags/${id}`, { tags })
      .success(data => deferred.resolve(data.data))
      .error((data, status, headers, config) => deferred.reject());
    }
  }
  Admin_ApiKeys_DataService_ApiTags.initClass();
  return Admin_ApiKeys_DataService_ApiTags;
});
