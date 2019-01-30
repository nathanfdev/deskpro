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
  let Admin_FeedbackCategories_DataService_FeedbackCategories;
  return (Admin_FeedbackCategories_DataService_FeedbackCategories = class Admin_FeedbackCategories_DataService_FeedbackCategories extends Admin_Main_DataService_Base {
    constructor(em, Api, $q) {
      super(em);
      this.$q   = $q;
      this.Api  = Api;

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

    loadList(model, reload) {

      if (this.loadListPromise) {
        return this.loadListPromise;
      }

      const deferred = this.$q.defer();

      if (!reload && this.recs.count()) {

        deferred.resolve(this.recs);
        return deferred.promise;
      }

      this.Api.sendGet('/feedback_categories').success( (data, status, headers, config) => {

        this._setListData(data.feedback_categories);
        return deferred.resolve(this.recs);
      }

      , (data, status, headers, config) => deferred.reject());

      this.loadListPromise = deferred.promise;

      return this.loadListPromise;
    }

    /**
        * Removes entity from entity manager
      *
      * @param id
    */

    remove(id) {

      const model = this.em.getById('feedback_category', id);

      if (model != null) {
        this.recs.remove(id);
        this.em.removeById('feedback_category', 'id');
      }

      return this._updateOrderOfData();
    }

    /*
     * Updates entity with new model data provided
     * with new model provided. Or adds it to the list if it doesnt exist.
     */
    updateModel(model) {

      // case of 'no parent'

      if (!model.options) {
        model.options = {parent_id: 0};
      }

      if (!model.options.parent_id || (model.options.parent_id === "0")) {
        model.options.parent_id = 0;
      }

      // this is due to the reason that in list it's stored as parent_id while in form it's stored in options.parent_id
      model.parent_id = model.options.parent_id;

      const new_model = this.em.createEntity('feedback_category', 'id', model);
      this.recs.set(new_model.id, new_model);

      return this._updateOrderOfData();
    }

    /*
     * Returns list of feedback_categories where feedback of specified feedback_category could be moved to
     * @param model - specified feedback_category model
     * @return array
     */
    getListOfMovables(model) {

      const move_list = [];
      const { parent_id } = model;

      this.recs.forEach((key, val) => {
        if (!this.hasChildren(val) && (val.id !== model.id)) {
          return move_list.push(val);
        }
      });

      return move_list;
    }

    /*
     * Returns list of parent records
     * @param model - specified model for which we want to know possible parent records
     * @return array
     */

    getListOfParents(model) {

      const parent_list = [{
        id: '',
        title: 'No Parent'
      }];

      this.recs.forEach( (key, val) => {

        if ((val.id !== model.id) && !val.parent_id) {
          return parent_list.push(val);
        }
      });

      return parent_list;
    }

    /*
     * Returns wherther spcified model has children or not
     * @param model - specified model for which we want to know if it has children or not
     * @return array
     */

    hasChildren(model) {

      for (let rec of Array.from(this.recs.values())) {

        if (~~rec.parent_id === model.id) {
          return true;
        }
      }

      return false;
    }

    /**
        * Creates entities for feedback categories raw data
        *
        * @return {Promise}
    */
    _setListData(raw_recs) {

      return (() => {
        const result = [];
        for (let rec of Array.from(raw_recs)) {

          const model = this.em.createEntity('feedback_category', 'id', rec);
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

      this.recs.reorder(function(a, b) {
        let left;
        const order1 = a.display_order || 0;
        const order2 = b.display_order || 0;

        if (order1 === order2) {
          return 0;
        }

        return ((left = order1 < order2)) != null ? left : -{1: 1};
      });

      return this.recs.notifyListeners('changed');
    }
  });
});