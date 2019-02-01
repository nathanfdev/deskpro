define([
  'DeskPRO/Util/Angular',
  'DeskPRO/Util/Arrays',
  'DeskPRO/Util/Util',
  'angular',
], function(
  Util_Angular,
  Arrays,
  Util,
  angular,
) {
  /*
   * This is a simple base data service that implements some default functionality for
   * loading object model
   */
  class Admin_Main_DataService_BaseModel {
    static initClass() {

      this.$inject = ['Api', '$q'];
    }

    constructor() {
      Util_Angular.setInjectedProperties(this, arguments);
      this.model             = {};
      this.init();
    }


    /*
     * An empty hook method for sub-classes
     */
    init() {
    }



    // model res endpoint
    url() {
      throw "This method must be implemented by a sub-class";
    }



    // map model from response
    resolveResponse(response) {
      return response;
    }



    // get model promise
    get(reload) {
      const deferred = this.$q.defer();

      if (this.loaded && (reload == null)) {
        deferred.resolve(this.model);
        return deferred.promise;
      }

      this._doGet().then(
        data => {
          this.loaded = true;
          return deferred.resolve(angular.copy(data, this.model));
        },
        res => {
          return deferred.reject(res);
      });

      return deferred.promise;
    }



    // load model api call
    _doGet() {
      const deferred = this.$q.defer();
      this.Api.sendGet(this.url()).success(data => {
        return deferred.resolve(this.resolveResponse(data));
    }).error((data, status, headers, config) => deferred.reject(data));

      return deferred.promise;
    }



    // update model
    set() {
      if (!this.loaded) { this.get(true); }

      const deferred = this.$q.defer();
      this._doSet().then(
        data => {
          return deferred.resolve(angular.copy(data, this.model));
        },
        res => {
          return deferred.reject(res);
      });

      return deferred.promise;
    }



    // update model api call
    _doSet() {
      const deferred = this.$q.defer();

      this.Api.sendPutJson(this.url(), this.model).success(data => {
        return deferred.resolve(this.resolveResponse(data));
    }).error((data, status, headers, config) => {
        return deferred.reject({
          info: data.error_message,
          status
        });
      });

      return deferred.promise;
    }
  }
  Admin_Main_DataService_BaseModel.initClass();
  return Admin_Main_DataService_BaseModel;
});

