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
        this.loadListPromise = null;
        this.recs = {
          active_statuses: new Admin_Main_Collection_OrderedDictionary(),
          closed_statuses: new Admin_Main_Collection_OrderedDictionary()
        };
      }

      /**
      		* Loads all feedback statuses
        	* Returns a promise.
        	*
        	* @return {Promise}
      */


      Admin_FeedbackStatuses_DataService_FeedbackStatuses.prototype.loadList = function(reload) {
        var deferred, http_def,
          _this = this;
        if (this.loadListPromise) {
          return this.loadListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.recs.active_statuses.count() && this.recs.closed_statuses.count()) {
          deferred.resolve(this.recs);
          return deferred.promise;
        }
        http_def = this.Api.sendGet('/feedback_statuses').success(function(data, status, headers, config) {
          _this._setListData(data.statuses);
          return deferred.resolve(_this.recs);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        this.loadListPromise = deferred.promise;
        return this.loadListPromise;
      };

      /**
      				* Removed entity from entity manager
      		  *
      		  * @param id
      */


      Admin_FeedbackStatuses_DataService_FeedbackStatuses.prototype.remove = function(id) {
        var model;
        model = this.em.getById('feedback_status', id);
        if ((model != null) && (model.status_type != null)) {
          this.recs[model.status_type + '_statuses'].remove(id);
          return this.em.removeById('feedback_status', 'id');
        }
      };

      /**
      				* Creates entities for feedback statuses raw data
      				* The thing is that it creates entities for both active and closed statuses
      				*
      				* @return {Promise}
      */


      Admin_FeedbackStatuses_DataService_FeedbackStatuses.prototype._setListData = function(raw_recs) {
        var model, rec, _i, _j, _len, _len1, _ref, _ref1, _results;
        _ref = raw_recs.active_statuses;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          rec = _ref[_i];
          model = this.em.createEntity('feedback_status', 'id', rec);
          model.retain();
          this.recs.active_statuses.set(model.id, model);
        }
        _ref1 = raw_recs.closed_statuses;
        _results = [];
        for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
          rec = _ref1[_j];
          model = this.em.createEntity('feedback_status', 'id', rec);
          model.retain();
          _results.push(this.recs.closed_statuses.set(model.id, model));
        }
        return _results;
      };

      /*
      				# Updates entity with new model data provided
      				# with new model provided. Or adds it to the list if it doesnt exist.
      */


      Admin_FeedbackStatuses_DataService_FeedbackStatuses.prototype.updateModel = function(model) {
        var new_model;
        new_model = this.em.createEntity('feedback_status', 'id', model);
        if ((model.status_type != null) && model.status_type === 'active') {
          this.recs.active_statuses.set(new_model.id, new_model);
        }
        if ((model.status_type != null) && model.status_type === 'closed') {
          this.recs.closed_statuses.set(new_model.id, new_model);
        }
        return new_model;
      };

      return Admin_FeedbackStatuses_DataService_FeedbackStatuses;

    })(Admin_Main_DataService_Base);
  });

}).call(this);

/*
//@ sourceMappingURL=FeedbackStatuses.js.map
*/