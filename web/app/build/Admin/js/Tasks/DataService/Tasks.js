(function() {
  define(function() {
    var Tasks;
    return Tasks = (function() {
      var _url;

      _url = '/tasks/settings';

      function Tasks(Api, $q) {
        this.Api = Api;
        this.$q = $q;
        this.settings = {};
      }

      Tasks.prototype.load = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet(_url).success((function(_this) {
          return function(data) {
            _this.settings = data;
            return deferred.resolve(_this.settings);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      Tasks.prototype.save = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendPutJson(_url, this.settings).success((function(_this) {
          return function(data) {
            return deferred.resolve();
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        return deferred.promise;
      };

      return Tasks;

    })();
  });

}).call(this);

//# sourceMappingURL=Tasks.js.map
