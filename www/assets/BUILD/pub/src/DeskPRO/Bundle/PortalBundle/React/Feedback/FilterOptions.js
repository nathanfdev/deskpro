import filter from 'lodash/filter';
import first from 'lodash/first';
import forEach from 'lodash/forEach';
import map from 'lodash/map';

export class FilterOptions {

  constructor(available) {
    this.status = available.status;
    this.status_categories = available.status_categories;
    this.types = available.types;
    this.sorts = available.sorts;
    this.sort_directions = available.sort_directions;
  }

  getStatusCategoryById(statusId, statusCategoryId) {
    const f = filter(this.status_categories[statusId], cat => cat.id === statusCategoryId);
    return first(f);
  }

  getStatusForStatusCategory(statusCategoryId) {
    let result = null;

    forEach(this.status_categories, (stCats, stId) => {
      forEach(stCats, (stCat) => {
        if (stCat.id === statusCategoryId) {
          result = stId;
        }
      });
    });
    return result;
  }

  getStatusCategoriesForStatus(statusId) {
    let result = [];
    forEach(this.status_categories, (stCats, stId) => {
      if (stId === statusId) {
        result = stCats;
      }
    });
    return result;
  }

  getStatusCategoryIdsForStatus(statusId) {
    return map(this.getStatusCategoriesForStatus(statusId), 'id');
  }
}
