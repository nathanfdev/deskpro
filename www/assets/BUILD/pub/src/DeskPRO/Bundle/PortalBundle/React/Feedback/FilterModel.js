import filter from 'lodash/filter';
import includes from 'lodash/includes';
import map from 'lodash/map';

export class FilterModel {

  constructor(data, available) {
    this.data = data;
    this.available = available;
    this.sort = data.sort;
    this.view = data.view;
    this.view_mode = data.view_mode;
    this.sort_direction = data.sort_direction || 'desc';
    this.status = data.status;
    this.q = data.q;
    this.activities = data.activities;
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
    this.view = 'list';
    this.view_mode = 'compact';
    this.q = '';
    this.status_categories = [];
    this.activities = [];
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

  getSelectedActivities() {
    return `/activity-${this.activities.join(',')}`;
  }

  createUrl() {
    let url = 'DP_URL/community/browse/';

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

    if (this.view) {
      url += `/view-${this.view}`;
    }

    if (this.view_mode) {
      url += `/viewmode-${this.view_mode}`;
    }

    if (this.activities.length > 0) {
      url += this.getSelectedActivities();
    }

    const query = [];

    if (this.q) {
      query.push(`q=${this.q}`);
    }

    if (this.page > 1) {
      query.push(`page=${this.page}`);
    }

    if (query.length) {
      url += `?${query.join('&')}`;
    }

    return url;
  }

  setPage(page) {
    this.page = page;
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

  toggleActivity(activity) {
    this.page = 1;

    if (includes(this.activities, activity)) {
      this.activities = filter(this.activities, n => n !== activity);
    } else {
      this.activities.push(activity);
    }
  }

  resetActivities() {
    this.page = 1;
    this.activities = [];
  }

  getStatus() {
    return this.status;
  }

  setViewMode(mode) {
    this.view_mode = mode;
  }

  getViewMode() {
    return this.view_mode;
  }

  setView(viewId) {
    this.view = viewId;
  }

  getView() {
    return this.view;
  }

  setQ(q) {
    this.page = 1;
    this.q = q;
  }

  getQ() {
    return this.q;
  }

  toggleType(typeId) {
    this.page = 1;
    const parsedTypeId = parseInt(typeId, 10);
    if (includes(this.types, parsedTypeId)) {
      this.types = filter(this.types, n => n !== parsedTypeId);
    } else {
      this.types.push(parsedTypeId);
    }
  }

  setType(typeId) {
    this.page = 1;
    const parsedTypeId = parseInt(typeId, 10);
    this.types = [];
    this.types.push(parsedTypeId);
  }
}
