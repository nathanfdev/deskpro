import filter from 'lodash/filter';
import includes from 'lodash/includes';
import map from 'lodash/map';

export class FilterModel {

  constructor(data, available) {
    this.data = data;
    this.available = available;
    this.sort = data.sort;
    this.sort_direction = data.sort_direction || 'desc';
    this.status = data.status;
    this.status_categories = map(data.status_categories, val => parseInt(val, 10));
    this.types = map(data.types, val => parseInt(val, 10));
    this.page = parseInt(data.page || 1, 10);
    this.checkEmptyStatusCategories();
  }

  reset() {
    this.page = 1;
    this.sort = 'date';
    this.sort_direction = 'desc';
    this.status = 'all';
    this.status_categories = [];
    this.types = map(this.data.types, val => parseInt(val, 10));
    this.checkEmptyStatusCategories();
  }

  changeSort(newSort) {
    const parts = newSort.split('-');
    if (parts.length === 2) {
      this.sort = parts[0];
      this.sort_direction = parts[1];
    }
    if (parts.length === 3) {
      this.sort = `${parts[0]}-${parts[1]}`;
      this.sort_direction = parts[2];
    }
  }

  checkEmptyStatusCategories() {
    if (this.status_categories.length === 0) {
      this.status_categories = this.available.getStatusCategoryIdsForStatus(this.status);
    }
    // if no status cats are picked and this status has some, check em all!
  }

  getSelectedTypes() {
    return `/type-${this.types.join(',')}`;
  }

  createUrl() {
    let url = 'DP_URL/feedback/browse/';

    url += this.status;

    if (this.status_categories.length > 0) {
      url += `-${this.status_categories.join(',')}`;
    }

    if (this.types.length > 0) {
      url += this.getSelectedTypes();
    }

    if (this.sort) {
      url += `/${this.sort}`;
      if (this.sort_direction) {
        url += `-${this.sort_direction}`;
      }
    }

    if (this.page > 1) {
      url += `?page=${this.page}`;
    }

    return url;
  }

  setStatus(status) {
    this.page = 1;
    if (this.status !== status) {
      this.status = status;
    }

    const avil = map(this.available.getStatusCategoriesForStatus(this.status), cat => cat.id);
    this.status_categories = filter(this.status_categories, cat => includes(avil, cat));
    this.checkEmptyStatusCategories();
  }

  setStatusCategory(rawCategory) {
    const category = parseInt(rawCategory, 10);

    this.page = 1;
    this.status_categories = [category];

    // force a filter on this.status_categories
    this.setStatus(this.available.getStatusForStatusCategory(category));
  }

  toggleStatusCategory(rawCategory) {
    const category = parseInt(rawCategory, 10);

    this.page = 1;
    if (includes(this.status_categories, category)) {
      this.status_categories = filter(this.status_categories, n => n !== category);
    } else {
      this.status_categories.push(category);
    }

    // force a filter on this.status_categories
    this.setStatus(this.available.getStatusForStatusCategory(category));
  }

  getStatus() {
    return this.status;
  }

  toggleType(typeId) {
    this.page = 1;
    typeId = parseInt(typeId, 10);
    if (includes(this.types, typeId)) {
      this.types = filter(this.types, n => n !== typeId);
    } else {
      this.types.push(typeId);
    }
  }

  setType(typeId) {
    this.page = 1;
    typeId = parseInt(typeId, 10);
    this.types = [];
    this.types.push(typeId);
  }
}
