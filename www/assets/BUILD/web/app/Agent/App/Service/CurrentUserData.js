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

      const d = this.$q.defer();
      const self = this;

      if (this.userInfo) {
        d.resolve(this.userInfo);
      } else {
        this.loadData().then(function () {
          return d.resolve(self.userInfo);
        }
        , (data, status) => d.reject(data, status));
      }

      return d.promise;
    }

    getUserPermissions() {
      if (this.promise) { return this.promise; }

      const d = this.$q.defer();
      const self = this;

      if (this.userPerms) {
        d.resolve(this.userPerms);
      } else {
        this.loadData().then(function () {
          return d.resolve(self.userPerms);
        }
        , (data, status) => d.reject(data, status));
      }

      return d.promise;
    }

    loadData() {
      if (this.httpPromise) {
        return this.httpPromise;
      }

      const self = this;
      this.httpPromise = this.$http.get('DP_URL/agent/me/info.js').success(function (data) {
        self.userInfo = data.agent;
        self.userPerms = data.perms;
      });

      return this.httpPromise;
    }
  }
  Agent_App_Service_CurrentUserData.initClass();

  return Agent_App_Service_CurrentUserData;
});
