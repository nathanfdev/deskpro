define([
  'Admin/Main/DataService/Base',
  'Admin/Main/Model/Base',
  'Admin/Main/Collection/OrderedDictionary'
], (
  Admin_Main_DataService_Base,
  Admin_Main_Model_Base,
  Admin_Main_Collection_OrderedDictionary
) => {
  class Admin_CommunityForums_DataService_CommunityForums extends Admin_Main_DataService_Base {
    constructor(em, Api, $q) {
      super(em);
      this.$q   = $q;
      this.Api  = Api;

      this.loadListPromise = null;
      this.recs = new Admin_Main_Collection_OrderedDictionary();
    }

    /**
    * Loads all community forums
      * Returns a promise.
      *
      * @return {Promise}
    */
    loadList(reload) {
      if (this.loadListPromise) {
        return this.loadListPromise;
      }

      const deferred = this.$q.defer();

      if (!reload && this.recs.count() && this.recs.count()) {
        deferred.resolve(this.recs);
        return deferred.promise;
      }

      const http_def = this.Api.sendGet('/community_forums').success((data, status, headers, config) => {
        this._setListData(data.types);
        return deferred.resolve(this.recs);
      }
      , (data, status, headers, config) => deferred.reject());

      this.loadListPromise = deferred.promise;

      return this.loadListPromise;
    }

    /**
        * Removed entity from entity manager
      *
      * @param id
    */

    remove(id) {
      const model = this.em.getById('community_forum', id);
      if (model != null) {
        this.recs.remove(id);
        this.em.removeById('community_forum', 'id');
      }

      return this._updateOrderOfData();
    }

    /*
    * Updates entity with new model data provided
  * with new model provided. Or adds it to the list if it doesnt exist.
  */
    updateModel(model) {
      const new_model = this.em.createEntity('community_forum', 'id', model);
      this.recs.set(new_model.id, new_model);

      this._updateOrderOfData();

      return new_model;
    }

    /*
    * Returns list of community_forums where community topic of specified community_forum could be moved to
  * @param model - specified community_forum model
    * @return array
    */

    getListOfMovables(model) {
      const move_list = [];

      this.recs.forEach((key, val) => {
        if (val.id !== model.id) {
          return move_list.push(val);
        }
      });

      return move_list;
    }

    /**
        * Creates entities for community forums raw data
        *
        * @return {Promise}
    */
    _setListData(raw_recs) {
      return (() => {
        const result = [];
        for (const rec of Array.from(raw_recs)) {
          const model = this.em.createEntity('community_forum', 'id', rec);
          model.retain();
          result.push(this.recs.set(model.id, model));
        }
        return result;
      })();
    }

    /*
    * Reorders the data of this data service
  * Useful for cases of drag&drop ordering of data
    */

    _updateOrderOfData() {
      this.recs.reorder((a, b) => {
        let left;
        const order1 = a.display_order || 0;
        const order2 = b.display_order || 0;

        if (order1 === order2) {
          return 0;
        }

        return ((left = order1 < order2)) != null ? left : -{ 1: 1 };
      });

      return this.recs.notifyListeners('changed');
    }
  }

  return Admin_CommunityForums_DataService_CommunityForums;
});
