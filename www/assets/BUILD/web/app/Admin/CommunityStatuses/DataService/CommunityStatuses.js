define([
  'Admin/Main/DataService/Base',
  'Admin/Main/Model/Base',
  'Admin/Main/Collection/OrderedDictionary'
], (
  Admin_Main_DataService_Base,
  Admin_Main_Model_Base,
  Admin_Main_Collection_OrderedDictionary
) => {
  class Admin_CommunityStatuses_DataService_CommunityStatuses extends Admin_Main_DataService_Base {
    constructor(em, Api, Api2, $q) {
      super(em);
      this.$q   = $q;
      this.Api  = Api;
      this.Api2  = Api2;

      this.loadListPromise = null;
      this.recs = {
        active_statuses: new Admin_Main_Collection_OrderedDictionary(),
        closed_statuses: new Admin_Main_Collection_OrderedDictionary()
      };
      this.perForumRecs = {
      };
    }

    /**
    * Loads all community statuses
      * Returns a promise.
      *
      * @return {Promise}
    */
    loadList(reload) {
      if (this.loadListPromise) {
        return this.loadListPromise;
      }

      const deferred = this.$q.defer();

      if (!reload && this.recs.active_statuses.count() && this.recs.closed_statuses.count()) {
        deferred.resolve(this.recs);
        return deferred.promise;
      }

      const http_def = this.Api.sendGet('/community_statuses').success((data, status, headers, config) => {
        this._setListData(data.statuses);
        return deferred.resolve(this.recs);
      }
      , (data, status, headers, config) => deferred.reject());

      this.loadListPromise = deferred.promise;

      return this.loadListPromise;
    }

    loadPerForumList(forumId) {
      const deferred = this.$q.defer();

      this.Api2.sendGet(
        `/community_forums/${forumId}/statuses`).success((data) => {
          this.perForumRecs[forumId] = data.data;
          return deferred.resolve(this.perForumRecs[forumId]);
        },
        () => deferred.reject());

      this.loadPerForumListPromise = deferred.promise;

      return this.loadPerForumListPromise;
    }

    /**
        * Removed entity from entity manager
      *
      * @param id
    */

    remove(id) {
      const model = this.em.getById('community_status', id);

      if ((model != null) && (model.status_type != null)) {
        this.recs[`${model.status_type}_statuses`].remove(id);
        this.em.removeById('community_status', 'id');
      }

      return this._updateOrderOfData();
    }

    /*
    * Updates entity with new model data provided
  * with new model provided. Or adds it to the list if it doesnt exist.
  */
    updateModel(model) {
      const new_model = this.em.createEntity('community_status', 'id', model);

      if ((model.status_type != null) && (model.status_type === 'active')) {
        this.recs.active_statuses.set(new_model.id, new_model);
      }

      if ((model.status_type != null) && (model.status_type === 'closed')) {
        this.recs.closed_statuses.set(new_model.id, new_model);
      }

      this._updateOrderOfData();

      return new_model;
    }

    /*
    * Returns list of community_statuses where community topic of specified community_status could be moved to
  * @param model - specified community_status model
    * @return array
    */

    getListOfMovables(model) {
      const move_list = [];

      this.recs[`${model.status_type}_statuses`].forEach((key, val) => {
        if (val.id !== model.id) {
          return move_list.push(val);
        }
      });

      return move_list;
    }

    /**
        * Creates entities for community statuses raw data
        * The thing is that it creates entities for both active and closed statuses
        *
        * @return {Promise}
    */
    _setListData(raw_recs) {
      let model;
      for (var rec of Array.from(raw_recs.active_statuses)) {
        model = this.em.createEntity('community_status', 'id', rec);
        model.retain();
        this.recs.active_statuses.set(model.id, model);
      }

      return (() => {
        const result = [];
        for (rec of Array.from(raw_recs.closed_statuses)) {
          model = this.em.createEntity('community_status', 'id', rec);
          model.retain();
          result.push(this.recs.closed_statuses.set(model.id, model));
        }
        return result;
      })();
    }

    _updateOrderOfData() {
      this.recs.active_statuses.reorder((a, b) => {
        let left;
        const order1 = a.display_order || 0;
        const order2 = b.display_order || 0;

        if (order1 === order2) {
          return 0;
        }

        return ((left = order1 < order2)) != null ? left : -{ 1: 1 };
      });

      this.recs.active_statuses.notifyListeners('changed');

      this.recs.closed_statuses.reorder((a, b) => {
        let left;
        const order1 = a.display_order || 0;
        const order2 = b.display_order || 0;

        if (order1 === order2) {
          return 0;
        }

        return ((left = order1 < order2)) != null ? left : -{ 1: 1 };
      });

      return this.recs.closed_statuses.notifyListeners('changed');
    }
  }

  return Admin_CommunityStatuses_DataService_CommunityStatuses;
});
