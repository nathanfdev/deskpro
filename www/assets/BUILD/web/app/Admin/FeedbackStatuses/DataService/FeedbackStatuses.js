// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS104: Avoid inline assignments
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/Base',
  'Admin/Main/Model/Base',
  'Admin/Main/Collection/OrderedDictionary'
], function(
  Admin_Main_DataService_Base,
  Admin_Main_Model_Base,
  Admin_Main_Collection_OrderedDictionary
)  {
  let Admin_FeedbackStatuses_DataService_FeedbackStatuses;
  return (Admin_FeedbackStatuses_DataService_FeedbackStatuses = class Admin_FeedbackStatuses_DataService_FeedbackStatuses extends Admin_Main_DataService_Base {
    constructor(em, Api, $q) {
      super(em);
      this.$q   = $q;
      this.Api  = Api;

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
    loadList(reload) {

      if (this.loadListPromise) {
        return this.loadListPromise;
      }

      const deferred = this.$q.defer();

      if (!reload && this.recs.active_statuses.count() && this.recs.closed_statuses.count()) {

        deferred.resolve(this.recs);
        return deferred.promise;
      }

      const http_def = this.Api.sendGet('/feedback_statuses').success( (data, status, headers, config) => {

        this._setListData(data.statuses);
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

      const model = this.em.getById('feedback_status', id);

      if ((model != null) && (model.status_type != null)) {
        this.recs[model.status_type + '_statuses'].remove(id);
        this.em.removeById('feedback_status', 'id');
      }

      return this._updateOrderOfData();
    }

    /*
    * Updates entity with new model data provided
  * with new model provided. Or adds it to the list if it doesnt exist.
  */
    updateModel(model) {

      const new_model = this.em.createEntity('feedback_status', 'id', model);

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
    * Returns list of feedback_statuses where feedback of specified feedback_status could be moved to
  * @param model - specified feedback_status model
    * @return array
    */

    getListOfMovables(model) {

      const move_list = [];

      this.recs[model.status_type + '_statuses'].forEach( (key, val) => {

        if (val.id !== model.id) {
          return move_list.push(val);
        }
      });

      return move_list;
    }

    /**
        * Creates entities for feedback statuses raw data
        * The thing is that it creates entities for both active and closed statuses
        *
        * @return {Promise}
    */
    _setListData(raw_recs) {

      let model;
      for (var rec of Array.from(raw_recs.active_statuses)) {

        model = this.em.createEntity('feedback_status', 'id', rec);
        model.retain();
        this.recs.active_statuses.set(model.id, model);
      }

      return (() => {
        const result = [];
        for (rec of Array.from(raw_recs.closed_statuses)) {

          model = this.em.createEntity('feedback_status', 'id', rec);
          model.retain();
          result.push(this.recs.closed_statuses.set(model.id, model));
        }
        return result;
      })();
    }

    _updateOrderOfData() {

      this.recs.active_statuses.reorder(function(a, b) {
        let left;
        const order1 = a.display_order || 0;
        const order2 = b.display_order || 0;

        if (order1 === order2) {
          return 0;
        }

        return ((left = order1 < order2)) != null ? left : -{1: 1};
      });

      this.recs.active_statuses.notifyListeners('changed');

      this.recs.closed_statuses.reorder(function(a, b) {
        let left;
        const order1 = a.display_order || 0;
        const order2 = b.display_order || 0;

        if (order1 === order2) {
          return 0;
        }

        return ((left = order1 < order2)) != null ? left : -{1: 1};
      });

      return this.recs.closed_statuses.notifyListeners('changed');
    }
  });
});