(function() {
  define(['DeskPRO/Util/Angular', 'DeskPRO/Util/Arrays', 'DeskPRO/Util/Util', 'angular'], function(Util_Angular, Arrays, Util, angular) {

    /*
    	 * This is a simple base data service that implements some default functionality for
    	 * loading object model
     */
    var Admin_Main_DataService_BaseModel;
    return Admin_Main_DataService_BaseModel = (function() {
      Admin_Main_DataService_BaseModel.$inject = ['Api', '$q'];

      function Admin_Main_DataService_BaseModel() {
        Util_Angular.setInjectedProperties(this, arguments);
        this.model = {};
        this.init();
      }


      /*
      		 * An empty hook method for sub-classes
       */

      Admin_Main_DataService_BaseModel.prototype.init = function() {};

      Admin_Main_DataService_BaseModel.prototype.url = function() {
        throw "This method must be implemented by a sub-class";
      };

      Admin_Main_DataService_BaseModel.prototype.resolveResponse = function(response) {
        return response;
      };

      Admin_Main_DataService_BaseModel.prototype.get = function(reload) {
        var deferred;
        deferred = this.$q.defer();
        if (this.loaded && (reload == null)) {
          deferred.resolve(this.model);
          return deferred.promise;
        }
        this._doGet().then((function(_this) {
          return function(data) {
            _this.loaded = true;
            return deferred.resolve(angular.copy(data, _this.model));
          };
        })(this), (function(_this) {
          return function(res) {
            return deferred.reject(res);
          };
        })(this));
        return deferred.promise;
      };

      Admin_Main_DataService_BaseModel.prototype._doGet = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendGet(this.url()).success((function(_this) {
          return function(data) {
            return deferred.resolve(_this.resolveResponse(data));
          };
        })(this)).error(function(data, status, headers, config) {
          return deferred.reject(data);
        });
        return deferred.promise;
      };

      Admin_Main_DataService_BaseModel.prototype.set = function() {
        var deferred;
        if (!this.loaded) {
          this.get(true);
        }
        deferred = this.$q.defer();
        this._doSet().then((function(_this) {
          return function(data) {
            return deferred.resolve(angular.copy(data, _this.model));
          };
        })(this), (function(_this) {
          return function(res) {
            return deferred.reject(res);
          };
        })(this));
        return deferred.promise;
      };

      Admin_Main_DataService_BaseModel.prototype._doSet = function() {
        var deferred;
        deferred = this.$q.defer();
        this.Api.sendPutJson(this.url(), this.model).success((function(_this) {
          return function(data) {
            return deferred.resolve(_this.resolveResponse(data));
          };
        })(this)).error((function(_this) {
          return function(data, status, headers, config) {
            return deferred.reject({
              info: data.error_message,
              status: status
            });
          };
        })(this));
        return deferred.promise;
      };

      return Admin_Main_DataService_BaseModel;

    })();
  });

}).call(this);

//# sourceMappingURL=BaseModel.js.map
