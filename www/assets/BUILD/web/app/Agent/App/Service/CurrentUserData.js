define(function() {
  class Agent_App_Service_CurrentUserData {
    static initClass() {
      this.$inject = ['$http', '$q'];
    }
    constructor($http, $q) {
      this.$http = $http;
      this.$q = $q;
      this.userInfo    = null;
      this.userPerms   = null;

      this.httpPromise = null;
    }

    getUserInfo() {
      if (this.promise) { return this.promise; }

      const d = this.$q.deferred();

      if (this.userInfo) {
        d.resolve(this.userInfo);
      } else {
        this.loadData().then(function () {
          return d.resolve(this.userInfo);
        }
        , (data, status) => d.reject(data, status));
      }

      return d.promise;
    }

    getUserPermissions() {
      if (this.promise) { return this.promise; }

      const d = this.$q.defer();

      if (this.userPerms) {
        d.resolve(this.userPerms);
      } else {
        this.loadData().then(function () {
          return d.resolve(this.userPerms);
        }
        , (data, status) => d.reject(data, status));
      }

      return d.promise;
    }

    loadData() {
      if (this.httpPromise) { return this.httpPromise; }

      this.httpPromise = this.$http.get('DP_URL/agent/me/info.js').success(function (data) {
        this.userInfo  = data.agent;
        return this.userPerms = data.perms;
      });

      return this.httpPromise;
    }
  }
  Agent_App_Service_CurrentUserData.initClass();

  return Agent_App_Service_CurrentUserData;
});
