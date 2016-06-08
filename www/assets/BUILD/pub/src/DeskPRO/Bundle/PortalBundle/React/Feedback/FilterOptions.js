import _ from 'lodash';

export class FilterOptions {

  constructor(available) {
    this.status = available.status;
    this.status_categories = available.status_categories;
    this.types = available.types;
    this.sorts = available.sorts;
    this.sort_directions = available.sort_directions;
  }

  getStatusCategoryById(status_id, status_category_id) {
    let f = _.filter(this.status_categories[status_id], (cat) => {
      return cat.id === status_category_id;
    });
    return _.first(f);
  }

  getStatusForStatusCategory(status_category_id) {
    let result = null;

    _.forEach(this.status_categories, (st_cats, st_id) => {
      _.forEach(st_cats, (st_cat) => {
        if (st_cat.id === status_category_id) {
          result = st_id;
        }
      });

    });
    return result;
  }

  getStatusCategoriesForStatus(status_id) {
    let result = [];
    _.forEach(this.status_categories, (st_cats, st_id) => {
      if (st_id === status_id) {
        result = st_cats;
      }
    });
    return result;
  }

  getStatusCategoryIdsForStatus(status_id) {
    return _.map(this.getStatusCategoriesForStatus(status_id), 'id');
  }
}
