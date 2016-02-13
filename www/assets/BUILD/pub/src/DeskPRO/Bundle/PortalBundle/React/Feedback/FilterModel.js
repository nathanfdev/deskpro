import _ from 'lodash';

export class FilterModel {

  constructor(data, available) {
    this.available = available;
    this.sort = data.sort;
    this.sort_direction = data.sort_direction || 'desc';
    this.status = data.status;
    this.status_categories = _.map(data.status_categories, function (val) {
      return _.parseInt(val);
    });
    this.types = _.map(data.types, function (val) {
      return _.parseInt(val);
    });
    this.page = _.parseInt(data.page || 1);
    this.checkEmptyTypes();
    this.checkEmptyStatusCategories();
  }

  reset() {
    this.page = 1;
    this.sort = 'date';
    this.sort_direction = 'desc';
    this.status = 'all';
    this.status_categories = [];
    this.types = [];
    this.checkEmptyTypes();
    this.checkEmptyStatusCategories();
  }

  changeSort(new_sort) {
    let parts = new_sort.split('-');
    if (parts.length == 2) {
      this.sort = parts[0];
      this.sort_direction = parts[1];
    }
    if (parts.length == 3) {
      this.sort = parts[0] + '-' + parts[1];
      this.sort_direction = parts[2];
    }
  }

  checkEmptyTypes() {
    if (this.types.length === 0) {
      // if no types are checked, default back to all types
      // this is the behaviour of the URL
      this.types = this.available.getAvailableTypeIds();
    }
  }

  checkEmptyStatusCategories() {
    if (this.status_categories.length === 0) {
      this.status_categories = this.available.getStatusCategoryIdsForStatus(this.status);
    }
    // if no status cats are picked and this status has some, check em all!
  }

  getAvailable() {
    return this.available;
  }

  createUrl() {
    let url = 'DP_URL/feedback/browse/';

    url += this.status;

    if (this.status_categories.length > 0) {
      url += '-' + this.status_categories.join(',');
    }

    if (this.types.length > 0) {
      let diff = _.difference(this.available.getAvailableTypeIds(), this.types);
      if (diff.length > 0) {
        // we only add the /type-x to the URL if it's a subset of types. default is to
        // include them all. if user has all selected, then we don't need it.
        // _.intersection above with a length of > 0 means the arrays have diff elements.
        url += '/type-';
        url += this.types.join(',');
      }
    }

    if (this.sort) {
      url += '/' + this.sort;
      if (this.sort_direction) {
        url += '-' + this.sort_direction;
      }
    }

    if (this.page > 1) {
      url += '?page=' + this.page;
    }

    return url;
  }

  setStatus(status_id) {
    this.page = 1;
    if (this.status != status_id) {
      this.status = status_id;
    }
    let avil = _.map(this.available.getStatusCategoriesForStatus(this.status), (cat) => {
      return cat.id;
    });
    this.status_categories = _.filter(this.status_categories, (cat) => {
      return _.includes(avil, cat);
    });
    this.checkEmptyStatusCategories();
  }

  getStatus() {
    return this.status;
  }

  toggleType(type_id) {
    this.page = 1;
    type_id = _.parseInt(type_id);
    if (_.includes(this.types, type_id)) {
      this.types = _.filter(this.types, (n) => {
        return n != type_id;
      });
    } else {
      this.types.push(type_id);
    }
    this.checkEmptyTypes();
  }

  setType(type_id) {
    this.page = 1;
    type_id = _.parseInt(type_id);
    this.types = [];
    this.types.push(type_id);
    this.checkEmptyTypes();
  }

  toggleStatusCategory(status_category_id) {
    this.page = 1;
    status_category_id = _.parseInt(status_category_id);
    if (_.includes(this.status_categories, status_category_id)) {
      this.status_categories = _.filter(this.status_categories, (n) => {
        return n != status_category_id;
      });
    } else {
      this.status_categories.push(status_category_id);
    }
    // force a filter on this.status_categories
    this.setStatus(this.available.getStatusForStatusCategory(status_category_id));
  }

  setStatusCategory(status_category_id) {
    this.page = 1;
    status_category_id = _.parseInt(status_category_id);
    this.status_categories = [];
    this.status_categories.push(status_category_id);
    // force a filter on this.status_categories
    this.setStatus(this.available.getStatusForStatusCategory(status_category_id));
  }
}
