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
  let Admin_ApiKeys_DataService_ApiTags;
  return Admin_ApiKeys_DataService_ApiTags = (function() {
    Admin_ApiKeys_DataService_ApiTags = class Admin_ApiKeys_DataService_ApiTags extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api2', '$q'];
      }

      getTags(id) {
        const deferred = this.$q.defer();
        this.Api2.sendGet(`/api_tags/${id}/flatten`)
        .success(data => {
          return deferred.resolve(data.data);
        })
        .error( (data, status, headers, config) => deferred.reject() );

        return deferred.promise;
      }
      updateTags(tags, id) {
        const deferred = this.$q.defer();
        return this.Api2.sendPutJson(`/api_tags/${id}`, {"tags": tags})
        .success(data => {
          return deferred.resolve(data.data);
        })
        .error( (data, status, headers, config) => deferred.reject() );
      }
    };
    Admin_ApiKeys_DataService_ApiTags.initClass();
    return Admin_ApiKeys_DataService_ApiTags;
  })();
});
