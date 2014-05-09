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
        var deferred, http_def;
        if (this.loadListPromise) {
          return this.loadListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.recs.active_statuses.count() && this.recs.closed_statuses.count()) {
          deferred.resolve(this.recs);
          return deferred.promise;
        }
        http_def = this.Api.sendGet('/feedback_statuses').success((function(_this) {
          return function(data, status, headers, config) {
            _this._setListData(data.statuses);
            return deferred.resolve(_this.recs);
          };
        })(this), function(data, status, headers, config) {
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
          this.em.removeById('feedback_status', 'id');
        }
        return this._updateOrderOfData();
      };


      /*
      		 * Updates entity with new model data provided
       	 * with new model provided. Or adds it to the list if it doesnt exist.
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
        this._updateOrderOfData();
        return new_model;
      };


      /*
      		 * Returns list of feedback_statuses where feedback of specified feedback_status could be moved to
       	 * @param model - specified feedback_status model
      		 * @return array
       */

      Admin_FeedbackStatuses_DataService_FeedbackStatuses.prototype.getListOfMovables = function(model) {
        var move_list;
        move_list = [];
        this.recs[model.status_type + '_statuses'].forEach((function(_this) {
          return function(key, val) {
            if (val.id !== model.id) {
              return move_list.push(val);
            }
          };
        })(this));
        return move_list;
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

      Admin_FeedbackStatuses_DataService_FeedbackStatuses.prototype._updateOrderOfData = function() {
        this.recs.active_statuses.reorder(function(a, b) {
          var order1, order2, _ref;
          order1 = a.display_order || 0;
          order2 = b.display_order || 0;
          if (order1 === order2) {
            return 0;
          }
          return (_ref = order1 < order2) != null ? _ref : -{
            1: 1
          };
        });
        this.recs.active_statuses.notifyListeners('changed');
        this.recs.closed_statuses.reorder(function(a, b) {
          var order1, order2, _ref;
          order1 = a.display_order || 0;
          order2 = b.display_order || 0;
          if (order1 === order2) {
            return 0;
          }
          return (_ref = order1 < order2) != null ? _ref : -{
            1: 1
          };
        });
        return this.recs.closed_statuses.notifyListeners('changed');
      };

      return Admin_FeedbackStatuses_DataService_FeedbackStatuses;

    })(Admin_Main_DataService_Base);
  });

}).call(this);

//# sourceMappingURL=FeedbackStatuses.js.map
