(function() {
  define(['DeskPRO/Util/Angular', 'DeskPRO/Util/Arrays', 'DeskPRO/Util/Util'], function(Util_Angular, Arrays, Util) {
    /*
       # This is a simple base data service that implements some default functionality for
       # loading the "list" collection, and some methods for keeping the list up to date.
    */

    var Admin_Main_DataService_BaseListEdit;
    return Admin_Main_DataService_BaseListEdit = (function() {
      function Admin_Main_DataService_BaseListEdit() {
        Util_Angular.setInjectedProperties(this, arguments);
        this.loadListPromise = null;
        this.isListLoaded = false;
        this.listModels = [];
        this.idProp = 'id';
        this.orderField = 'display_order';
        this.init();
      }

      /*
        	# An empty hook method for sub-classes
      */


      Admin_Main_DataService_BaseListEdit.prototype.init = function() {};

      /*
      		# Loads list of accounts
        	#
        	# @return {Promise}
      */


      Admin_Main_DataService_BaseListEdit.prototype.loadList = function(reload) {
        var deferred,
          _this = this;
        if (reload) {
          this.loadListPromise = null;
          this.isListLoaded = false;
        }
        if (this.loadListPromise) {
          return this.loadListPromise;
        }
        if (this.isListLoaded) {
          deferred = this.$q.defer();
          deferred.resolve(this.listModels);
          return deferred.promise;
        }
        deferred = this.$q.defer();
        this.loadListPromise = deferred.promise;
        this._doLoadList().then(function(models) {
          _this.isListLoaded = true;
          _this._setListData(models);
          return deferred.resolve(_this.listModels);
        }, function() {
          return deferred.reject();
        });
        return this.loadListPromise;
      };

      /*
        	# Sets ist data on the @listModels object
      */


      Admin_Main_DataService_BaseListEdit.prototype._setListData = function(listModels) {
        var model, _i, _len, _results;
        this.listModels.length = 0;
        _results = [];
        for (_i = 0, _len = listModels.length; _i < _len; _i++) {
          model = listModels[_i];
          _results.push(this.listModels.push(model));
        }
        return _results;
      };

      /*
        	# Find a model that has been loaded into the list
        	#
        	# @param {Integer} id
        	# @return {Object}
      */


      Admin_Main_DataService_BaseListEdit.prototype.findListModelById = function(id) {
        var child, model, _i, _j, _len, _len1, _ref, _ref1;
        _ref = this.listModels;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          model = _ref[_i];
          if (model[this.idProp] === id) {
            return model;
          }
          if (model.children) {
            _ref1 = model.children;
            for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
              child = _ref1[_j];
              if (child[this.idProp] === id) {
                return child;
              }
            }
          }
        }
        return null;
      };

      Admin_Main_DataService_BaseListEdit.prototype.returnIndexForModel = function(obj) {
        var idx, model, _i, _len, _ref;
        _ref = this.listModels;
        for (idx = _i = 0, _len = _ref.length; _i < _len; idx = ++_i) {
          model = _ref[idx];
          if (model[this.idProp] === obj[this.idProp]) {
            return idx;
          }
        }
        return null;
      };

      /*
        	# This method should be overriden.
        	#
        	# This method needs to load the list data and needs to
        	# resolve to an array of models that will be set on the list collection.
        	#
        	# This method must return a promise
        	#
        	# @return {promise}
      */


      Admin_Main_DataService_BaseListEdit.prototype._doLoadList = function() {
        throw new Exception("This method must be implemented by a sub-class");
      };

      /*
      		# Takes a data model and updates the list.
        	# For example, you would use this when you want to apply changes from the Edit pane into the List pane.
        	# By merging the data model, this will either 1) update the list model (eg the title) or 2) create
        	# a new list model and append it to the list.
        	#
        	# You should always supply a dataMapper. The default implementation is to just get the id/title properties
        	# from teh dataModel which may not be sufficient.
        	#
        	# @param {Object} dataModel
        	# @param {Function} dataMapper Optionally supply a function that can create the listModel for cases we need to append it to the list
      */


      Admin_Main_DataService_BaseListEdit.prototype.mergeDataModel = function(dataModel, dataMapper) {
        var child, idx, k, listModel, model, newListModel, oldParent, parent, removeIdx, v, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2;
        if (dataMapper == null) {
          dataMapper = null;
        }
        if (!this.isListLoaded) {
          return;
        }
        listModel = null;
        oldParent = null;
        _ref = this.listModels;
        for (idx = _i = 0, _len = _ref.length; _i < _len; idx = ++_i) {
          model = _ref[idx];
          if (model[this.idProp] === dataModel[this.idProp]) {
            listModel = model;
            break;
          }
          if (model.children) {
            _ref1 = model.children;
            for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
              child = _ref1[_j];
              if (child[this.idProp] === dataModel[this.idProp]) {
                oldParent = model;
                listModel = child;
                break;
              }
            }
          }
        }
        if (listModel !== null) {
          for (k in listModel) {
            v = listModel[k];
            if (dataModel[k] != null) {
              listModel[k] = dataModel[k];
            }
          }
          if ((oldParent != null) && oldParent[this.idProp] !== dataModel.parent_id) {
            _ref2 = oldParent.children;
            for (idx = _k = 0, _len2 = _ref2.length; _k < _len2; idx = ++_k) {
              model = _ref2[idx];
              if (model[this.idProp] === dataModel[this.idProp]) {
                removeIdx = idx;
                break;
              }
            }
            if (removeIdx != null) {
              oldParent.children.splice(removeIdx, 1);
            }
            if (dataModel.parent_id != null) {
              parent = this.findListModelById(dataModel.parent_id);
              return parent.children.push(dataModel);
            } else {
              return this.listModels.push(dataModel);
            }
          } else {
            if (dataModel.parent_id != null) {
              parent = this.findListModelById(dataModel.parent_id);
              parent.children.push(dataModel);
              removeIdx = this.returnIndexForModel(dataModel);
              if (Util.isNumber(removeIdx)) {
                return this.listModels.splice(removeIdx, 1);
              }
            }
          }
        } else {
          if (dataMapper) {
            newListModel = dataMapper(dataModel);
          } else {
            newListModel = {
              id: dataModel[this.idProp],
              title: dataModel.title
            };
          }
          return this.listModels.push(newListModel);
        }
      };

      /*
        	# Remove a model from the list by ID.
        	#
        	# @return {Object/null} The removed object or null if object could not be found
      */


      Admin_Main_DataService_BaseListEdit.prototype.removeListModelById = function(id) {
        var idx, model, removeIdx, result, _i, _len, _ref;
        if (!this.isListLoaded) {
          return;
        }
        removeIdx = null;
        _ref = this.listModels;
        for (idx = _i = 0, _len = _ref.length; _i < _len; idx = ++_i) {
          model = _ref[idx];
          if (model[this.idProp] === id) {
            removeIdx = idx;
            break;
          }
        }
        result = null;
        if (removeIdx !== null) {
          result = this.listModels.splice(removeIdx, 1);
          result = result[0];
        }
        return result;
      };

      /*
        	# Re-orders the list collection
      */


      Admin_Main_DataService_BaseListEdit.prototype.reorderList = function() {
        var _this = this;
        if (!this.isListLoaded) {
          return;
        }
        this.listModels.sort(function(data1, data2) {
          var o1, o2, _ref;
          if (data1[_this.orderField]) {
            o1 = data1[_this.orderField];
          } else {
            o1 = data[_this.idProp];
          }
          if (data2[_this.orderField]) {
            o2 = data2[_this.orderField];
          } else {
            o2 = data2[_this.idProp];
          }
          if (o1 === o2) {
            return 0;
          }
          return (_ref = o1 < o2) != null ? _ref : -{
            1: 1
          };
        });
        return this.listModels.reverse();
      };

      return Admin_Main_DataService_BaseListEdit;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=BaseListEdit.js.map
*/