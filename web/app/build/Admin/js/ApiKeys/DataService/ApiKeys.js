(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var ApiKeys;
    return ApiKeys = (function(_super) {
      __extends(ApiKeys, _super);

      function ApiKeys() {
        return ApiKeys.__super__.constructor.apply(this, arguments);
      }

      ApiKeys.$inject = ['Api', '$q'];

      ApiKeys.prototype.url = function() {
        return '/api_keys';
      };


      /*
      		 * Generate new API key code
      	 	 *
      	   * @param {Object} model api_key model
      	   * @return {promise}
       */

      ApiKeys.prototype.regenerateApiKey = function(model) {
        return this.Api.sendPostJson('/api_keys/regenerate/' + model.id).success((function(_this) {
          return function(data) {
            model.code = data.code;
            model.keyString = data.keyString;
            return {
              replayLogEntry: function(entry) {
                var deferred;
                deferred = this.$q.defer();
                this.Api.sendGet("/api_keys/replay/" + entry.id).success((function(_this) {
                  return function(data, status, headers, config) {
                    _this.loadList(true);
                    return deferred.resolve(data, status);
                  };
                })(this)).error(function(data, status, headers, config) {
                  return deferred.reject(data, status);
                });
                return deferred.promise;
              }
            };
          };
        })(this));
      };

      return ApiKeys;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=ApiKeys.js.map
