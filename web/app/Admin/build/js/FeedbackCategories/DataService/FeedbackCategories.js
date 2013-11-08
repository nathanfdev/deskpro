(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/Base', 'Admin/Main/Model/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Main_DataService_Base, Admin_Main_Model_Base, Admin_Main_Collection_OrderedDictionary) {
    var Admin_FeedbackCategories_DataService_FeedbackCategories;
    return Admin_FeedbackCategories_DataService_FeedbackCategories = (function(_super) {
      __extends(Admin_FeedbackCategories_DataService_FeedbackCategories, _super);

      function Admin_FeedbackCategories_DataService_FeedbackCategories(em, Api, $q) {
        Admin_FeedbackCategories_DataService_FeedbackCategories.__super__.constructor.call(this, em);
        this.$q = $q;
        this.Api = Api;
        this.loadListPromise = null;
        this.recs = new Admin_Main_Collection_OrderedDictionary();
      }

      /**
      		* Loads list of records
       	*
      * @param reload - (optional) whether to reload list of records or no
      *
      * @return {Promise}
      */


      Admin_FeedbackCategories_DataService_FeedbackCategories.prototype.loadList = function(model, reload) {
        var deferred,
          _this = this;
        if (this.loadListPromise) {
          return this.loadListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.recs.count()) {
          deferred.resolve(this.recs);
          return deferred.promise;
        }
        this.Api.sendGet('/feedback_categories').success(function(data, status, headers, config) {
          _this._setListData(data.feedback_categories);
          return deferred.resolve(_this.recs);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        this.loadListPromise = deferred.promise;
        return this.loadListPromise;
      };

      /**
      				* Removes entity from entity manager
      		  *
      		  * @param id
      */


      Admin_FeedbackCategories_DataService_FeedbackCategories.prototype.remove = function(id) {
        var model;
        model = this.em.getById('feedback_category', id);
        if (model != null) {
          this.recs.remove(id);
          this.em.removeById('feedback_category', 'id');
        }
        return this._updateOrderOfData();
      };

      /*
      		# Updates entity with new model data provided
       	# with new model provided. Or adds it to the list if it doesnt exist.
      */


      Admin_FeedbackCategories_DataService_FeedbackCategories.prototype.updateModel = function(model) {
        var new_model;
        if (!model.options.parent_id || model.options.parent_id === "0") {
          model.options.parent_id = 0;
        }
        model.parent_id = model.options.parent_id;
        new_model = this.em.createEntity('feedback_category', 'id', model);
        this.recs.set(new_model.id, new_model);
        return this._updateOrderOfData();
      };

      /*
      		# Returns list of feedback_categories where feedback of specified feedback_category could be moved to
       	# @param model - specified feedback_category model
      		# @return array
      */


      Admin_FeedbackCategories_DataService_FeedbackCategories.prototype.getListOfMovables = function(model) {
        var move_list,
          _this = this;
        move_list = [];
        this.recs.forEach(function(key, val) {
          if (val.id !== model.id) {
            return move_list.push(val);
          }
        });
        return move_list;
      };

      /*
      		# Returns list of parent records
      		# @param model - specified model for which we want to know possible parent records
      		# @return array
      */


      Admin_FeedbackCategories_DataService_FeedbackCategories.prototype.getListOfParents = function(model) {
        var parent_list,
          _this = this;
        parent_list = [
          {
            id: 0,
            title: 'No Parent'
          }
        ];
        this.recs.forEach(function(key, val) {
          if (val.id !== model.id && !val.parent_id) {
            return parent_list.push(val);
          }
        });
        return parent_list;
      };

      /**
      				* Creates entities for feedback categories raw data
      				*
      				* @return {Promise}
      */


      Admin_FeedbackCategories_DataService_FeedbackCategories.prototype._setListData = function(raw_recs) {
        var model, rec, _i, _len, _results;
        _results = [];
        for (_i = 0, _len = raw_recs.length; _i < _len; _i++) {
          rec = raw_recs[_i];
          model = this.em.createEntity('feedback_category', 'id', rec);
          model.retain();
          _results.push(this.recs.set(model.id, model));
        }
        return _results;
      };

      /*
      		# Reorders the data of this data service
       	# Useful for cases of drag&drop ordering of data
      */


      Admin_FeedbackCategories_DataService_FeedbackCategories.prototype._updateOrderOfData = function() {
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

      return Admin_FeedbackCategories_DataService_FeedbackCategories;

    })(Admin_Main_DataService_Base);
  });

}).call(this);

/*
//@ sourceMappingURL=FeedbackCategories.js.map
*/