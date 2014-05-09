(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/Base', 'Admin/Main/Model/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Main_DataService_Base, Admin_Main_Model_Base, Admin_Main_Collection_OrderedDictionary) {
    var Admin_FeedbackTypes_DataService_FeedbackTypes;
    return Admin_FeedbackTypes_DataService_FeedbackTypes = (function(_super) {
      __extends(Admin_FeedbackTypes_DataService_FeedbackTypes, _super);

      function Admin_FeedbackTypes_DataService_FeedbackTypes(em, Api, $q) {
        Admin_FeedbackTypes_DataService_FeedbackTypes.__super__.constructor.call(this, em);
        this.$q = $q;
        this.Api = Api;
        this.loadListPromise = null;
        this.recs = new Admin_Main_Collection_OrderedDictionary();
      }


      /**
      		* Loads all feedback types
        	* Returns a promise.
        	*
        	* @return {Promise}
       */

      Admin_FeedbackTypes_DataService_FeedbackTypes.prototype.loadList = function(reload) {
        var deferred, http_def;
        if (this.loadListPromise) {
          return this.loadListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.recs.count() && this.recs.count()) {
          deferred.resolve(this.recs);
          return deferred.promise;
        }
        http_def = this.Api.sendGet('/feedback_types').success((function(_this) {
          return function(data, status, headers, config) {
            _this._setListData(data.types);
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

      Admin_FeedbackTypes_DataService_FeedbackTypes.prototype.remove = function(id) {
        var model;
        model = this.em.getById('feedback_type', id);
        if (model != null) {
          this.recs.remove(id);
          this.em.removeById('feedback_type', 'id');
        }
        return this._updateOrderOfData();
      };


      /*
      		 * Updates entity with new model data provided
       	 * with new model provided. Or adds it to the list if it doesnt exist.
       */

      Admin_FeedbackTypes_DataService_FeedbackTypes.prototype.updateModel = function(model) {
        var new_model;
        new_model = this.em.createEntity('feedback_type', 'id', model);
        this.recs.set(new_model.id, new_model);
        this._updateOrderOfData();
        return new_model;
      };


      /*
      		 * Returns list of feedback_types where feedback of specified feedback_type could be moved to
       	 * @param model - specified feedback_type model
      		 * @return array
       */

      Admin_FeedbackTypes_DataService_FeedbackTypes.prototype.getListOfMovables = function(model) {
        var move_list;
        move_list = [];
        this.recs.forEach((function(_this) {
          return function(key, val) {
            if (val.id !== model.id) {
              return move_list.push(val);
            }
          };
        })(this));
        return move_list;
      };


      /**
      				* Creates entities for feedback types raw data
      				*
      				* @return {Promise}
       */

      Admin_FeedbackTypes_DataService_FeedbackTypes.prototype._setListData = function(raw_recs) {
        var model, rec, _i, _len, _results;
        _results = [];
        for (_i = 0, _len = raw_recs.length; _i < _len; _i++) {
          rec = raw_recs[_i];
          model = this.em.createEntity('feedback_type', 'id', rec);
          model.retain();
          _results.push(this.recs.set(model.id, model));
        }
        return _results;
      };


      /*
      		 * Reorders the data of this data service
       	 * Useful for cases of drag&drop ordering of data
       */

      Admin_FeedbackTypes_DataService_FeedbackTypes.prototype._updateOrderOfData = function() {
        this.recs.reorder(function(a, b) {
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
        return this.recs.notifyListeners('changed');
      };

      return Admin_FeedbackTypes_DataService_FeedbackTypes;

    })(Admin_Main_DataService_Base);
  });

}).call(this);

//# sourceMappingURL=FeedbackTypes.js.map
