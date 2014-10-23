(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/BaseListEdit'], function(BaseListEdit) {
    var Admin_ApiKeys_DataService_ApiKeys;
    return Admin_ApiKeys_DataService_ApiKeys = (function(_super) {
      __extends(Admin_ApiKeys_DataService_ApiKeys, _super);

      function Admin_ApiKeys_DataService_ApiKeys() {
        return Admin_ApiKeys_DataService_ApiKeys.__super__.constructor.apply(this, arguments);
      }

      Admin_ApiKeys_DataService_ApiKeys.$inject = ['Api', '$q'];

      Admin_ApiKeys_DataService_ApiKeys.prototype.url = function() {
        return '/api_keys';
      };

      Admin_ApiKeys_DataService_ApiKeys.prototype.replayLogEntry = function(entry) {
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
      };


      /*
      		 * Generate new API key code
      		 *
      		 * @param {Object} model api_key model
      		 * @return {promise}
       */

      Admin_ApiKeys_DataService_ApiKeys.prototype.regenerateApiKey = function(model) {
        return this.Api.sendPostJson('/api_keys/regenerate/' + model.id).success((function(_this) {
          return function(data) {
            model.code = data.code;
            return model.keyString = data.keyString;
          };
        })(this));
      };

      return Admin_ApiKeys_DataService_ApiKeys;

    })(BaseListEdit);
  });

}).call(this);

//# sourceMappingURL=ApiKeys.js.map
