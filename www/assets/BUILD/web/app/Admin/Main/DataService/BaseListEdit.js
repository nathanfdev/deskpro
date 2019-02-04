define([
  'DeskPRO/Util/Angular',
  'DeskPRO/Util/Arrays',
  'DeskPRO/Util/Util',
  'angular'
], (
  Util_Angular,
  Arrays,
  Util,
  angular
) => {
  /*
   * This is a simple base data service that implements some default functionality for
   * loading the "list" collection, and some methods for keeping the list up to date.
   */
  // todo update models after reload instead of creating new
  class Admin_Main_DataService_BaseListEdit {
    constructor() {
      Util_Angular.setInjectedProperties(this, arguments);
      this.map = {};
      this.loadListPromise   = null;
      this.isListLoaded      = false;
      this.listModels        = [];
      this.idProp            = 'id';
      this.orderField        = 'display_order';
      this.subLists          = [];
      this.pagination        = {};
      this.isReloadWaiting   = false;
      this.init();
    }


    /*
     * An empty hook method for sub-classes
     */
    init() {
    }


    /*
      * If data has changed, then the next time this list
      * is loaded should be new
      */
    setReloadNext() {
      return this.isReloadWaiting = true;
    }


    /*
     * Loads list of accounts
     *
     * @return {Promise}
     */
    loadList(reload, params) {
      let deferred;
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

      this._doLoadList(params).then((models) => {
        this.isListLoaded = true;
        this._setListData(models);
        this._setPaginationData(models);
        return deferred.resolve(this.listModels);
      }
      , () => deferred.reject());

      return this.loadListPromise;
    }

    refreshList() {
      const deferred = this.$q.defer();
      this.loadListPromise = deferred.promise;

      this._doRefreshList().then((models) => {
        this._setListData(models);
        this._setPaginationData(models);
        return deferred.resolve(this.listModels);
      }
      , () => deferred.reject());
      return this.loadListPromise;
    }


    /*
     * This is useful if we need to store 2 or more lists instead of default one
     * Call this method somewhere ( init() method of data service is preferable) and use array of names of sub lists
     *
     * @param {Array} subLists - array with names of sub lists (eg. ['email_data', 'ip_data'])
     */
    setSubLists(subLists) {
      if (Util.isArray(subLists)) { return this.subLists = subLists; }
    }


    /*
     * Sets list data on the @listModels object
     */
    _setListData(listModels) {
      this.listModels.length = 0;

      if (!listModels) { return; }

      if (this.subLists.length) {
        this.listModels = {};
        return (() => {
          const result = [];
          for (var subModel of Array.from(this.subLists)) {
            this.listModels[subModel] = [];

            if (listModels[subModel]) {
              result.push((() => {
                const result1 = [];
                for (const model of Array.from(listModels[subModel])) {
                  result1.push(this.listModels[subModel].push(model));
                }
                return result1;
              })());
            } else {
              result.push(undefined);
            }
          }
          return result;
        })();
      }
      return (() => {
        const result2 = [];
        for (const model of Array.from(listModels)) {
          result2.push(this._addModel(model));
        }
        return result2;
      })();
    }


    /*
     * Sets pagination data for current data service
     *
     * @param {Object} listModels - object representing the list
     */
    _setPaginationData(listModels) {
      if (!listModels) { return; }

      // we assume that backend returned appropriate pagination info and doesn't check its correctness here
      if (listModels.pagination) { this.pagination = listModels.pagination; }
      // in case of no data from backend just skip pagination step
      if (Util.isEmpty(this.pagination)) { return; }

      if (this.subLists.length) {
        return (() => {
          const result = [];
          for (var subModel of Array.from(this.subLists)) {
            this.pagination[subModel].page = this.pagination[subModel].page || '1';
            this.pagination[subModel].page_nums = [];
            result.push((() => {
              const result1 = [];
              for (let i = 0, end = this.pagination[subModel].num_pages, asc = end >= 0; asc ? i < end : i > end; asc ? i++ : i--) {
                result1.push(this.pagination[subModel].page_nums.push(i + 1));
              }
              return result1;
            })());
          }
          return result;
        })();
      }
      this.pagination.page_nums = [];
      return (() => {
        const result2 = [];
        for (let i = 0, end1 = this.pagination.num_pages, asc1 = end1 >= 0; asc1 ? i <= end1 : i >= end1; asc1 ? i++ : i--) {
          result2.push(this.pagination.page_nums.push(i + 1));
        }
        return result2;
      })();
    }

    /*
     * @return {Object} returns information about pagination
     */
    getPagination() {
      return this.pagination;
    }

    /*
     * Find a model that has been loaded into the list
     *
     * @param {Integer} id
     * @return {Object}
     */
    findListModelById(id) {
      let model;
      if (this.subLists.length) {
        for (const subModel of Array.from(this.subLists)) {
          for (model of Array.from(this.listModels[subModel])) {
            if (model[this.idProp] === id) {
              return model;
            }
          }
        }
      } else {
        for (model of Array.from(this.listModels)) {
          if (model[this.idProp] === id) {
            return model;
          }
          if (model.children) {
            for (const child of Array.from(model.children)) {
              if (child[this.idProp] === id) {
                return child;
              }
            }
          }
        }
      }
      return null;
    }

    /*
     * Find children of specified object
     *
     * @param {Object} obj - specified object in which we'll search
     * @param {Integet} id - id of children we want to search
     */
    findChildModelById(obj, id) {
      if (obj.children) {
        for (const model of Array.from(obj.children)) {
          if (model[this.idProp] === id) {
            return model;
          }
        }
      }

      return null;
    }

    /*
     * Returns index of specified model
     *
     * @param {Object} obj
     * @return {Object}
     */
    returnIndexForModel(obj) {
      for (let idx = 0; idx < this.listModels.length; idx++) {
        const model = this.listModels[idx];
        if (model[this.idProp] === obj[this.idProp]) {
          return idx;
        }
      }

      return null;
    }

    /*
     * Checks whether an object has children or not
     *
     * @param {Object} obj
     * @return {Boolean}
     */
    hasChildren(obj) {
      const model = this.findListModelById(obj.id);

      if (model && model.children && model.children.length) {
        return true;
      }

      return false;
    }

    /*
     * Checks whether obj has children and form changed its value since it was created, could be useful in some cases
     *
     * @param {Object} obj - model object
     * @param {Object} form - form object
     * @return {Boolean}
     */
    hasChildrenAndChangedParent(obj, form) {
      const value1 = parseInt(obj.original_parent_id || 0);
      const value2 = parseInt(form.parent_id);

      if (this.hasChildren(obj) && (parseInt(value1) !== parseInt(value2))) {
        return true;
      }

      return false;
    }

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
    mergeDataModel(dataModel, dataMapper = null, subList = null) {
      let idx,
        model,
        parent;
      if (!this.isListLoaded) { return; }

      let listModel = null;
      let oldParent = null;

      if (subList) {
        for (idx = 0; idx < this.listModels[subList].length; idx++) {
          model = this.listModels[subList][idx];
          if (model[this.idProp] === dataModel[this.idProp]) {
            listModel = model;
            break;
          }
          if (dataModel.old_id && (model[this.idProp] === dataModel.old_id)) {
            listModel = model;
            break;
          }
        }
      } else {
        for (idx = 0; idx < this.listModels.length; idx++) {
          model = this.listModels[idx];
          if (model[this.idProp] === dataModel[this.idProp]) {
            listModel = model;
            break;
          }
          if (dataModel.old_id && (model[this.idProp] === dataModel.old_id)) {
            listModel = model;
            break;
          }

          if (model.children) {
            for (const child of Array.from(model.children)) {
              if (child[this.idProp] === dataModel[this.idProp]) {
                oldParent = model;
                listModel = child;
                break;
              }
            }
          }
        }
      }

      // if this model is already in list then we some options
      if (listModel !== null) {
        let removeIdx;
        for (const k of Object.keys(listModel || {})) {
          const v = listModel[k];
          if (dataModel[k] !== undefined) {
            listModel[k] = dataModel[k];
          }
        }

        // case of changing the parent AND if model already has parent - have to re-populate sub-tree with children
        if ((oldParent != null) && (oldParent[this.idProp] !== dataModel.parent_id)) {
          for (idx = 0; idx < oldParent.children.length; idx++) {
            model = oldParent.children[idx];
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

        // case of changing the parent AND if model previously didn't have a parent - should add this model as child
        } else if (dataModel.parent_id != null) {
            // first - add new model as child to the parent model (if it's not already there - this is for cases when parent not changed)
          parent = this.findListModelById(dataModel.parent_id);
          const existingChild =  this.findChildModelById(parent, dataModel.id);

          if (!existingChild) { parent.children.push(dataModel); }

            // second - delete this child from top-level list
          removeIdx = this.returnIndexForModel(dataModel);
          if (Util.isNumber(removeIdx)) { this.listModels.splice(removeIdx, 1); }
        }
      } else {
        // this is case of model that doesn't exist in the list yet
        let newListModel;
        if (dataMapper) {
          newListModel = dataMapper(dataModel);
        } else if (dataModel.parent_id != null) {
          parent = this.findListModelById(dataModel.parent_id);
          if (!parent.children) { parent.children = []; }
          parent.children.push(dataModel);
        } else {
          newListModel = dataModel;
          newListModel.children = [];
        }

        if (newListModel && !subList) { this.listModels.push(newListModel); }
        if (newListModel && subList) { this.listModels[subList].push(newListModel); }
      }

      if (dataModel.old_id) { return dataModel.old_id = dataModel[this.idProp]; }
    }


    /*
     * Remove a model from the list by ID.
     *
     * @return {Object/null} The removed object or null if object could not be found
     */
    removeListModelById(id) {
      let idx,
        model;
      if (!this.isListLoaded) { return; }

      let removeIdx = null;
      let subModelIdx = null;

      if (this.subLists.length) {
        for (const subModel of Array.from(this.subLists)) {
          for (idx = 0; idx < this.listModels[subModel].length; idx++) {
            model = this.listModels[subModel][idx];
            if (model[this.idProp] === id) {
              removeIdx = idx;
              subModelIdx = subModel;
              break;
            }
          }
        }
      } else {
        for (idx = 0; idx < this.listModels.length; idx++) {
          model = this.listModels[idx];
          if (model[this.idProp] === id) {
            removeIdx = idx;
            break;
          }
        }
      }

      let result = null;

      if (removeIdx !== null) {
        if (subModelIdx) { result = this.listModels[subModelIdx].splice(removeIdx, 1); }
        if (!subModelIdx) { result = this.listModels.splice(removeIdx, 1); }
        result = result[0];
      }

      return result;
    }


    /*
     * Re-orders the list collection
     */
    reorderList() {
      if (!this.isListLoaded) { return; }

      this.listModels.sort((data1, data2) => {
        let left,
          o1,
          o2;
        if (data1[this.orderField]) {
          o1 = data1[this.orderField];
        } else {
          o1 = data[this.idProp];
        }

        if (data2[this.orderField]) {
          o2 = data2[this.orderField];
        } else {
          o2 = data2[this.idProp];
        }

        if (o1 === o2) {
          return 0;
        }

        return ((left = o1 < o2)) != null ? left : -{ 1: 1 };
      });
      return this.listModels.reverse();
    }


    // add new model to list/map
    _addModel(model) {
      if (!model || (model[this.idProp] == null)) { return null; }
      if (this.map[model[this.idProp]] != null) {
        angular.copy(model, this.map[model[this.idProp]]); // update exist model
        if (this.listModels.indexOf(this.map[model[this.idProp]]) === -1) {
          this.listModels.push(this.map[model[this.idProp]]);
        }
      } else {
        this.map[model[this.idProp]] = model;
        this.listModels.push(model);
      }

      return model;
    }


    // remove model from list/map
    _removeModel(model) {
      if ((model[this.idProp] == null)) { return null; }
      model = this.map[model[this.idProp]];
      if ((model == null)) { return null; }

      delete this.map[model[this.idProp]];
      const idx = this.listModels.indexOf(model);
      if (idx > -1) { return this.listModels.splice(idx, 1); }
    }


    // simple proxy
    all(reload, params) {
      return this.loadList(reload, params);
    }


    // simple proxy
    get(id) {
      const deferred = this.$q.defer();
      this.all().then(() => {
        if (!id) { deferred.resolve(null); }
        return deferred.resolve(this.map[id]);
      }
      , () => deferred.resolve(this.map[id] || null));

      return deferred.promise;
    }


    // update/create model
    set(data) {
      const def = this.$q.defer();

      this._doSave(data).then(
        data => def.resolve(this._addModel(data)),
        res => def.reject(res));

      return def.promise;
    }


    // remove model
    remove(model) {
      const def = this.$q.defer();

      this._doRemove(model).then(
        () => def.resolve(this._removeModel(model)),
        res => def.reject(res));

      return def.promise;
    }


    url() {
      throw new Error('[BaseListEdit:url] This method must be implemented by a sub-class');
    }


    resolveResponse(response) {
      return response;
    }


    // overriden by child classes for back compatibiliy
    _doLoadList(params) {
      const deferred = this.$q.defer();
      if ((params == null)) { params = {}; }

      this.Api.sendGet(this.url(), params).success(data => deferred.resolve(this.resolveResponse(data))).error((data, status, headers, config) => deferred.reject(data));

      return deferred.promise;
    }


    _doSave(model) {
      const deferred = this.$q.defer();
      let method = 'sendPutJson'; // is new
      if ((model[this.idProp] != null) && model[this.idProp]) { method = 'sendPostJson'; }

      const id = model[this.idProp] || 0;
      this.Api[method](`${this.url()}/${id}`, model).success(data => deferred.resolve(this.resolveResponse(data))).error((data, status, headers, config) => deferred.reject({
        info: data.error_message,
        status
      }));

      return deferred.promise;
    }


    _doRemove(model) {
      const deferred = this.$q.defer();

      const id = model[this.idProp] || 0;
      this.Api.sendDelete(`${this.url()}/${id}`).success(() => deferred.resolve()).error((data, status, headers, config) => deferred.reject({
        info: data.error_message,
        status
      }));

      return deferred.promise;
    }
  }

  return Admin_Main_DataService_BaseListEdit;
});
