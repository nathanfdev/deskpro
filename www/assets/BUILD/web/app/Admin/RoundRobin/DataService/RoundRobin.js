// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
  'angular'
], function(
  Admin_Main_DataService_BaseListEdit,
  angular
)  {
  const settings = {};
  class Admin_RoundRobin_DataService_RoundRobin extends Admin_Main_DataService_BaseListEdit {
    static initClass() {
      this.$inject = ['Api', '$q'];
    }

    url() { return '/round_robin'; }

    getSettings(reload) {
      const def = this.$q.defer();

      if ((settings.enabled != null) && (reload == null)) {
        def.resolve(settings);
      } else {
        this.Api.sendGet(this.url() + '/settings').then(data => {
          angular.copy(data.data, settings);
          return def.resolve(settings);
        });
      }

      return def.promise;
    }


    saveSettings() {
      return this.Api.sendPutJson(this.url() + '/settings', settings);
    }



    checkTriggers(id) {
      let url = this.url() + '/triggers';
      if (id != null) { url += `/${id}`; }
      const def = this.$q.defer();

      this.Api.sendGet(url).then(data => {
        return def.resolve(data.data);
      });

      return def.promise;
    }
  }
  Admin_RoundRobin_DataService_RoundRobin.initClass();
  return Admin_RoundRobin_DataService_RoundRobin;
});
