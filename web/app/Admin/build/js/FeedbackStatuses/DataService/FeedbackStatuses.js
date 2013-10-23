(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/Base', 'Admin/Main/Model/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Main_DataService_Base, Admin_Main_Model_Base, Admin_Main_Collection_OrderedDictionary) {
    var Admin_FeedbackStatuses_DataService_FeedbackStatuses;
    return Admin_FeedbackStatuses_DataService_FeedbackStatuses = (function(_super) {
      __extends(Admin_FeedbackStatuses_DataService_FeedbackStatuses, _super);

      function Admin_FeedbackStatuses_DataService_FeedbackStatuses(em, Api, $q) {
        Admin_FeedbackStatuses_DataService_FeedbackStatuses.__super__.constructor.call(this, em);
        this.$q = $q;
        this.Api = Api;
        this.loadStatusesListPromise = null;
        this.statuses = new Admin_Main_Collection_OrderedDictionary();
      }

      /**
      		* Loads all feedback statuses
        	* Returns a promise.
        	*
        	* @return {Promise}
      */


      Admin_FeedbackStatuses_DataService_FeedbackStatuses.prototype.loadStatusesList = function(reload) {
        var deferred, http_def,
          _this = this;
        if (this.loadStatusesListPromise) {
          return this.loadStatusesListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.statuses.count()) {
          deferred.resolve(this.statuses);
          return deferred.promise;
        }
        http_def = this.Api.sendGet('/feedback_statuses').success(function(data, status, headers, config) {
          _this.statuses = data.statuses;
          return deferred.resolve(_this.statuses);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        this.loadStatusesListPromise = deferred.promise;
        return this.loadStatusesListPromise;
      };

      return Admin_FeedbackStatuses_DataService_FeedbackStatuses;

    })(Admin_Main_DataService_Base);
  });

}).call(this);

/*
//@ sourceMappingURL=FeedbackStatuses.js.map
*/