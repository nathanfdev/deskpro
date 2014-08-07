(function() {
  define(['DeskPRO/Util/Angular', 'DeskPRO/Util/Arrays', 'DeskPRO/Util/Util', 'angular'], function(Util_Angular, Arrays, Util, angular) {

    /*
    	 * This is a simple base data service that implements some default functionality for
    	 * loading the "list" collection, and some methods for keeping the list up to date.
     */
    var Admin_Main_DataService_BaseListEdit;
    return Admin_Main_DataService_BaseListEdit = (function() {
      function Admin_Main_DataService_BaseListEdit() {
        Util_Angular.setInjectedProperties(this, arguments);
        this.map = {};
        this.loadListPromise = null;
        this.isListLoaded = false;
        this.listModels = [];
        this.idProp = 'id';
        this.orderField = 'display_order';
        this.subLists = [];
        this.pagination = {};
        this.isReloadWaiting = false;
        this.init();
      }


      /*
      		 * An empty hook method for sub-classes
       */

      Admin_Main_DataService_BaseListEdit.prototype.init = function() {};


      /*
        	 * If data has changed, then the next time this list
        	 * is loaded should be new
       */

      Admin_Main_DataService_BaseListEdit.prototype.setReloadNext = function() {
        return this.isReloadWaiting = true;
      };


      /*
      		 * Loads list of accounts
      		 *
      		 * @return {Promise}
       */

      Admin_Main_DataService_BaseListEdit.prototype.loadList = function(reload) {
        var deferred;
        if (reload || this.isReloadWaiting) {
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
        this._doLoadList().then((function(_this) {
          return function(models) {
            _this.isListLoaded = true;
            _this._setListData(models);
            _this._setPaginationData(models);
            return deferred.resolve(_this.listModels);
          };
        })(this), (function(_this) {
          return function() {
            return deferred.reject();
          };
        })(this));
        return this.loadListPromise;
      };

      Admin_Main_DataService_BaseListEdit.prototype.refreshList = function() {
        var deferred;
        deferred = this.$q.defer();
        this.loadListPromise = deferred.promise;
        this._doRefreshList().then((function(_this) {
          return function(models) {
            _this._setListData(models);
            _this._setPaginationData(models);
            return deferred.resolve(_this.listModels);
          };
        })(this), (function(_this) {
          return function() {
            return deferred.reject();
          };
        })(this));
        return this.loadListPromise;
      };


      /*
      		 * This is useful if we need to store 2 or more lists instead of default one
      		 * Call this method somewhere ( init() method of data service is preferable) and use array of names of sub lists
      		 *
      		 * @param {Array} subLists - array with names of sub lists (eg. ['email_data', 'ip_data'])
       */

      Admin_Main_DataService_BaseListEdit.prototype.setSubLists = function(subLists) {
        if (Util.isArray(subLists)) {
          return this.subLists = subLists;
        }
      };


      /*
      		 * Sets ist data on the @listModels object
       */

      Admin_Main_DataService_BaseListEdit.prototype._setListData = function(listModels) {
        var model, subModel, _i, _j, _len, _len1, _ref, _results, _results1;
        this.listModels.length = 0;
        if (!listModels) {
          return;
        }
        if (this.subLists.length) {
          this.listModels = {};
          _ref = this.subLists;
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            subModel = _ref[_i];
            this.listModels[subModel] = [];
            if (listModels[subModel]) {
              _results.push((function() {
                var _j, _len1, _ref1, _results1;
                _ref1 = listModels[subModel];
                _results1 = [];
                for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
                  model = _ref1[_j];
                  _results1.push(this.listModels[subModel].push(model));
                }
                return _results1;
              }).call(this));
            } else {
              _results.push(void 0);
            }
          }
          return _results;
        } else {
          _results1 = [];
          for (_j = 0, _len1 = listModels.length; _j < _len1; _j++) {
            model = listModels[_j];
            _results1.push(this._addModel(model));
          }
          return _results1;
        }
      };


      /*
      		 * Sets pagination data for current data service
      		 *
      		 * @param {Object} listModels - object representing the list
       */

      Admin_Main_DataService_BaseListEdit.prototype._setPaginationData = function(listModels) {
        var i, subModel, _i, _j, _len, _ref, _ref1, _results, _results1;
        if (!listModels) {
          return;
        }
        if (listModels.pagination) {
          this.pagination = listModels.pagination;
        }
        if (Util.isEmpty(this.pagination)) {
          return;
        }
        if (this.subLists.length) {
          _ref = this.subLists;
          _results = [];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            subModel = _ref[_i];
            this.pagination[subModel].page = this.pagination[subModel].page || "1";
            this.pagination[subModel].page_nums = [];
            _results.push((function() {
              var _j, _ref1, _results1;
              _results1 = [];
              for (i = _j = 0, _ref1 = this.pagination[subModel].num_pages; 0 <= _ref1 ? _j < _ref1 : _j > _ref1; i = 0 <= _ref1 ? ++_j : --_j) {
                _results1.push(this.pagination[subModel].page_nums.push(i + 1));
              }
              return _results1;
            }).call(this));
          }
          return _results;
        } else {
          this.pagination.page_nums = [];
          _results1 = [];
          for (i = _j = 0, _ref1 = this.pagination.num_pages; 0 <= _ref1 ? _j <= _ref1 : _j >= _ref1; i = 0 <= _ref1 ? ++_j : --_j) {
            _results1.push(this.pagination.page_nums.push(i + 1));
          }
          return _results1;
        }
      };


      /*
      		 *	@return {Object} returns information about pagination
       */

      Admin_Main_DataService_BaseListEdit.prototype.getPagination = function() {
        return this.pagination;
      };


      /*
      		 * Find a model that has been loaded into the list
      		 *
      		 * @param {Integer} id
      		 * @return {Object}
       */

      Admin_Main_DataService_BaseListEdit.prototype.findListModelById = function(id) {
        var child, model, subModel, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref2, _ref3;
        if (this.subLists.length) {
          _ref = this.subLists;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            subModel = _ref[_i];
            _ref1 = this.listModels[subModel];
            for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
              model = _ref1[_j];
              if (model[this.idProp] === id) {
                return model;
              }
            }
          }
        } else {
          _ref2 = this.listModels;
          for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
            model = _ref2[_k];
            if (model[this.idProp] === id) {
              return model;
            }
            if (model.children) {
              _ref3 = model.children;
              for (_l = 0, _len3 = _ref3.length; _l < _len3; _l++) {
                child = _ref3[_l];
                if (child[this.idProp] === id) {
                  return child;
                }
              }
            }
          }
        }
        return null;
      };


      /*
      		 * Find children of specified object
      		 *
      		 * @param {Object} obj - specified object in which we'll search
      		 * @param {Integet} id - id of children we want to search
       */

      Admin_Main_DataService_BaseListEdit.prototype.findChildModelById = function(obj, id) {
        var model, _i, _len, _ref;
        if (obj.children) {
          _ref = obj.children;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            model = _ref[_i];
            if (model[this.idProp] === id) {
              return model;
            }
          }
        }
        return null;
      };


      /*
      		 * Returns index of specified model
      		 *
      		 * @param {Object} obj
      		 * @return {Object}
       */

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
      		 * Checks whether an object has children or not
      		 *
      		 * @param {Object} obj
      		 * @return {Boolean}
       */

      Admin_Main_DataService_BaseListEdit.prototype.hasChildren = function(obj) {
        var model;
        model = this.findListModelById(obj.id);
        if (model && model.children && model.children.length) {
          return true;
        }
        return false;
      };


      /*
      		 * Checks whether obj has children and form changed its value since it was created, could be useful in some cases
      		 *
      		 *	@param {Object} obj - model object
      		 * @param {Object} form - form object
      		 * @return {Boolean}
       */

      Admin_Main_DataService_BaseListEdit.prototype.hasChildrenAndChangedParent = function(obj, form) {
        var value1, value2;
        value1 = parseInt(obj.original_parent_id || 0);
        value2 = parseInt(form.parent_id);
        if (this.hasChildren(obj) && parseInt(value1) !== parseInt(value2)) {
          return true;
        }
        return false;
      };


      /*
      		 * This method should be overriden.
      		 *
      		 * This method needs to load the list data and needs to
      		 * resolve to an array of models that will be set on the list collection.
      		 *
      		 * This method must return a promise
      		 *
      		 * @return {promise}
       */

      Admin_Main_DataService_BaseListEdit.prototype._doLoadList = function() {
        throw new Exception("This method must be implemented by a sub-class");
      };


      /*
      		 * Takes a data model and updates the list.
      		 * For example, you would use this when you want to apply changes from the Edit pane into the List pane.
      		 * By merging the data model, this will either 1) update the list model (eg the title) or 2) create
      		 * a new list model and append it to the list.
      		 *
      		 * You should always supply a dataMapper. The default implementation is to just get the id/title properties
      		 * from teh dataModel which may not be sufficient.
      		 *
      		 * @param {Object} dataModel
      		 * @param {Function} dataMapper Optionally supply a function that can create the listModel for cases we need to append it to the list
      		 * @param {String} subList Optional parameter in case we want to update only sub list
       */

      Admin_Main_DataService_BaseListEdit.prototype.mergeDataModel = function(dataModel, dataMapper, subList) {
        var child, existingChild, idx, k, listModel, model, newListModel, oldParent, parent, removeIdx, v, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref2, _ref3;
        if (dataMapper == null) {
          dataMapper = null;
        }
        if (subList == null) {
          subList = null;
        }
        if (!this.isListLoaded) {
          return;
        }
        listModel = null;
        oldParent = null;
        if (subList) {
          _ref = this.listModels[subList];
          for (idx = _i = 0, _len = _ref.length; _i < _len; idx = ++_i) {
            model = _ref[idx];
            if (model[this.idProp] === dataModel[this.idProp]) {
              listModel = model;
              break;
            }
            if (dataModel.old_id && model[this.idProp] === dataModel.old_id) {
              listModel = model;
              break;
            }
          }
        } else {
          _ref1 = this.listModels;
          for (idx = _j = 0, _len1 = _ref1.length; _j < _len1; idx = ++_j) {
            model = _ref1[idx];
            if (model[this.idProp] === dataModel[this.idProp]) {
              listModel = model;
              break;
            }
            if (dataModel.old_id && model[this.idProp] === dataModel.old_id) {
              listModel = model;
              break;
            }
            if (model.children) {
              _ref2 = model.children;
              for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
                child = _ref2[_k];
                if (child[this.idProp] === dataModel[this.idProp]) {
                  oldParent = model;
                  listModel = child;
                  break;
                }
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
            _ref3 = oldParent.children;
            for (idx = _l = 0, _len3 = _ref3.length; _l < _len3; idx = ++_l) {
              model = _ref3[idx];
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
              parent.children.push(dataModel);
            } else {
              this.listModels.push(dataModel);
            }
          } else {
            if (dataModel.parent_id != null) {
              parent = this.findListModelById(dataModel.parent_id);
              existingChild = this.findChildModelById(parent, dataModel.id);
              if (!existingChild) {
                parent.children.push(dataModel);
              }
              removeIdx = this.returnIndexForModel(dataModel);
              if (Util.isNumber(removeIdx)) {
                this.listModels.splice(removeIdx, 1);
              }
            }
          }
        } else {
          if (dataMapper) {
            newListModel = dataMapper(dataModel);
          } else {
            if (dataModel.parent_id != null) {
              parent = this.findListModelById(dataModel.parent_id);
              if (!parent.children) {
                parent.children = [];
              }
              parent.children.push(dataModel);
            } else {
              newListModel = dataModel;
              newListModel.children = [];
            }
          }
          if (newListModel && !subList) {
            this.listModels.push(newListModel);
          }
          if (newListModel && subList) {
            this.listModels[subList].push(newListModel);
          }
        }
        if (dataModel.old_id) {
          return dataModel.old_id = dataModel[this.idProp];
        }
      };


      /*
      		 * Remove a model from the list by ID.
      		 *
      		 * @return {Object/null} The removed object or null if object could not be found
       */

      Admin_Main_DataService_BaseListEdit.prototype.removeListModelById = function(id) {
        var idx, model, removeIdx, result, subModel, subModelIdx, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2;
        if (!this.isListLoaded) {
          return;
        }
        removeIdx = null;
        subModelIdx = null;
        if (this.subLists.length) {
          _ref = this.subLists;
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            subModel = _ref[_i];
            _ref1 = this.listModels[subModel];
            for (idx = _j = 0, _len1 = _ref1.length; _j < _len1; idx = ++_j) {
              model = _ref1[idx];
              if (model[this.idProp] === id) {
                removeIdx = idx;
                subModelIdx = subModel;
                break;
              }
            }
          }
        } else {
          _ref2 = this.listModels;
          for (idx = _k = 0, _len2 = _ref2.length; _k < _len2; idx = ++_k) {
            model = _ref2[idx];
            if (model[this.idProp] === id) {
              removeIdx = idx;
              break;
            }
          }
        }
        result = null;
        if (removeIdx !== null) {
          if (subModelIdx) {
            result = this.listModels[subModelIdx].splice(removeIdx, 1);
          }
          if (!subModelIdx) {
            result = this.listModels.splice(removeIdx, 1);
          }
          result = result[0];
        }
        return result;
      };


      /*
      		 * Re-orders the list collection
       */

      Admin_Main_DataService_BaseListEdit.prototype.reorderList = function() {
        if (!this.isListLoaded) {
          return;
        }
        this.listModels.sort((function(_this) {
          return function(data1, data2) {
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
          };
        })(this));
        return this.listModels.reverse();
      };

      Admin_Main_DataService_BaseListEdit.prototype._addModel = function(model) {
        if (model[this.idProp] == null) {
          return null;
        }
        if (this.map[model[this.idProp]] != null) {
          angular.copy(model, this.map[model[this.idProp]]);
          if (this.listModels.indexOf(this.map[model[this.idProp]]) === -1) {
            this.listModels.push(this.map[model[this.idProp]]);
          }
        } else {
          this.map[model[this.idProp]] = model;
          this.listModels.push(model);
        }
        return model;
      };

      Admin_Main_DataService_BaseListEdit.prototype._removeModel = function(model) {
        var idx;
        if (model[this.idProp] == null) {
          return null;
        }
        model = this.map[model[this.idProp]];
        if (model == null) {
          return null;
        }
        delete this.map[model[this.idProp]];
        idx = this.listModels.indexOf(model);
        if (idx > -1) {
          return this.listModels.splice(idx, 1);
        }
      };

      Admin_Main_DataService_BaseListEdit.prototype.all = function() {
        return this.loadList();
      };

      Admin_Main_DataService_BaseListEdit.prototype.get = function(id) {
        var deferred;
        if (id == null) {
          return null;
        }
        deferred = this.$q.defer();
        this.loadList().then((function(_this) {
          return function() {
            return deferred.resolve(_this.map[id] || null);
          };
        })(this));
        return deferred.promise;
      };

      Admin_Main_DataService_BaseListEdit.prototype.set = function(data) {
        var def;
        def = this.$q.defer();
        this._doSave(data).then((function(_this) {
          return function(data) {
            return def.resolve(_this._addModel(data));
          };
        })(this), (function(_this) {
          return function(res) {
            return def.reject(res);
          };
        })(this));
        return def.promise;
      };

      Admin_Main_DataService_BaseListEdit.prototype.remove = function(model) {
        var def;
        def = this.$q.defer();
        this._doRemove(model).then((function(_this) {
          return function() {
            return def.resolve(_this._removeModel(model));
          };
        })(this), (function(_this) {
          return function(res) {
            return def.reject(res);
          };
        })(this));
        return def.promise;
      };

      Admin_Main_DataService_BaseListEdit.prototype.url = function() {
        throw new Exception("This method must be implemented by a sub-class");
      };

      Admin_Main_DataService_BaseListEdit.prototype.resolveResponse = function(response) {
        return response;
      };

      Admin_Main_DataService_BaseListEdit.prototype._doLoadList = function() {
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

      Admin_Main_DataService_BaseListEdit.prototype._doSave = function(model) {
        var deferred, id, method;
        deferred = this.$q.defer();
        method = 'sendPostJson';
        if ((model[this.idProp] != null) && model[this.idProp]) {
          method = 'sendPutJson';
        }
        id = model[this.idProp] || 0;
        this.Api[method](this.url() + ("/" + id), model).success((function(_this) {
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

      Admin_Main_DataService_BaseListEdit.prototype._doRemove = function(model) {
        var deferred, id;
        deferred = this.$q.defer();
        id = model[this.idProp] || 0;
        this.Api.sendDelete(this.url() + ("/" + id)).success((function(_this) {
          return function() {
            return deferred.resolve();
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

      return Admin_Main_DataService_BaseListEdit;

    })();
  });

}).call(this);

//# sourceMappingURL=BaseListEdit.js.map
