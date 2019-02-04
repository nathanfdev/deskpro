define(() => {
  const _url = '/problems/settings';
  class Problems {
    constructor(Api, $q) {
      this.Api = Api;
      this.$q = $q;
      this.settings = {};
    }


    load() {
      const deferred = this.$q.defer();

      this.Api.sendGet(_url).success(
        (data) => {
          this.settings = data;
          return deferred.resolve(this.settings);
        },
        (data, status, headers, config) => deferred.reject());

      return deferred.promise;
    }

    save() {
      const deferred = this.$q.defer();

      this.Api.sendPutJson(_url, this.settings).success(data => deferred.resolve()
      , (data, status, headers, config) => deferred.reject());

      return deferred.promise;
    }
  }
  return Problems;
});
