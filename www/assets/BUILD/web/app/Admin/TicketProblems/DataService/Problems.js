/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  let Problems;
  return Problems = (function() {
    let _url = undefined;
    Problems = class Problems {
      static initClass() {
  
        _url = '/problems/settings';
      }

      constructor(Api, $q) {
        this.Api = Api;
        this.$q = $q;
        this.settings = {};
      }


      load() {
        const deferred = this.$q.defer();

        this.Api.sendGet(_url).success(
          data => {
            this.settings = data;
            return deferred.resolve(this.settings);
          },
          (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }

      save() {
        const deferred = this.$q.defer();

        this.Api.sendPutJson(_url, this.settings).success( data => {
          return deferred.resolve();
        }
        , (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }
    };
    Problems.initClass();
    return Problems;
  })();
});
