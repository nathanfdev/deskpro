import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import * as AppActions from "DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/ActionTypes";
import * as FeedbackListActions from "../Actions/FeedbackListActions";

import { Reducer } from "Ampliflux/reducers";

export default class FeedbackList extends Reducer {
  getInitialState() {
    return {
      viewMode: constants.VIEW_MODE_TABLE,
      viewModeOptions: [
        {field: constants.VIEW_MODE_TABLE, label: 'Table view', icon:'fa-table'}, {field: constants.VIEW_MODE_LIST, label: 'List view', icon:'fa-list'}
      ],
      query: {awaiting_validation: 1},
      filters: {
        name: 'type',
        alias: 'category',
        value: ''
      },
      filterValues: [/* string */],
      sort: 'date_created', /* Order By ... */
      sortName: 'Date', /* Label for Order By... */
      order: constants.ORDER_DESC, /* Asc, Desc */
      sortOptions: [
        {field: 'date_created', label: 'Date', icon:'fa-calendar-o'},
        {field: 'total_rating', label: 'Rating', icon:'fa-calendar-o'},
        {field: 'num_ratings', label: 'Number of votes', icon:'fa-calendar-o'}
      ],
      listViewFields: [
        {name: 'id', label: 'ID', status: constants.FIELD_REQUIRED, priority: 3},
        {name: 'status', label: 'Status', status: constants.FIELD_REQUIRED, priority: 2},
        {name: 'hidden_status', label: 'Hidden status', status: constants.FIELD_REQUIRED, priority: 1},
        {name: 'title', label: 'Title', status: constants.FIELD_HIDDEN, priority: 4},
        {name: 'status_category', label: 'Status category', status: constants.FIELD_HIDDEN, priority: 5},
        {name: 'author_name', label: 'Submitter', status: constants.FIELD_HIDDEN, priority: 6},
        {name: 'language_id', label: 'Lang', status: constants.FIELD_HIDDEN, priority: 7},
        {name: 'type', label: 'Type', status: constants.FIELD_HIDDEN, priority: 8},
        {name: 'slug', label: 'Slug', status: constants.FIELD_HIDDEN, priority: 9},
        {name: 'date_created', label: 'Created', status: constants.FIELD_SHOWN, priority: 0},
        {name: 'date_published', label: 'Published', status: constants.FIELD_SHOWN, priority: 0},
        {name: 'view_count', label: 'Views', status: constants.FIELD_SHOWN, priority: 0},
        {name: 'total_rating', label: 'Rating', status: constants.FIELD_SHOWN, priority: 0},
        {name: 'num_rating', label: 'Votes', status: constants.FIELD_SHOWN, priority: 10},
        {name: 'num_comments', label: 'Comments', status: constants.FIELD_SHOWN, priority: 11},
        {name: 'validating', label: 'Validating', status: constants.FIELD_SHOWN, priority: 12},
        {name: 'popularity', label: 'Popularity', status: constants.FIELD_SHOWN, priority: 13},
        {name: 'content', label: 'Content', status: constants.FIELD_SHOWN, priority: 14},
        {name: 'custom_category', label: 'Category', status: constants.FIELD_HIDDEN, priority: 15}
      ],
      tableViewFields: [
        {name: 'id', label: 'ID', className: 'id-col', status: constants.FIELD_SHOWN, priority: 3},
        {name: 'status', label: 'Status', status: constants.FIELD_SHOWN, priority: 2},
        {name: 'hidden_status', label: 'Hidden status', status: constants.FIELD_SHOWN, priority: 1},
        {name: 'title', label: 'Title', className: 'item-title', status: constants.FIELD_SHOWN, priority: 4},
        {name: 'status_category', label: 'Status category', status: constants.FIELD_SHOWN, priority: 5},
        {name: 'author_name', label: 'Submitter', className: 'user-col', status: constants.FIELD_SHOWN, priority: 6},
        {name: 'language_id', label: 'Lang', status: constants.FIELD_HIDDEN, priority: 7},
        {name: 'type', label: 'Type', status: constants.FIELD_HIDDEN, priority: 8},
        {name: 'slug', label: 'Slug', status: constants.FIELD_HIDDEN, priority: 9},
        {name: 'date_created', label: 'Created', status: constants.FIELD_SHOWN, priority: 10},
        {name: 'date_published', label: 'Published', status: constants.FIELD_SHOWN, priority: 11},
        {name: 'view_count', label: 'Views', status: constants.FIELD_SHOWN, priority: 12},
        {name: 'total_rating', label: 'Rating', status: constants.FIELD_SHOWN, priority: 13},
        {name: 'num_rating', label: 'Votes', status: constants.FIELD_SHOWN, priority: 14},
        {name: 'num_comments', label: 'Comments', status: constants.FIELD_SHOWN, priority: 15},
        {name: 'validating', label: 'Validating', status: constants.FIELD_SHOWN, priority: 16},
        {name: 'popularity', label: 'Popularity', status: constants.FIELD_SHOWN, priority: 17},
        {name: 'content', label: 'Content', status: constants.FIELD_SHOWN, priority: 18},
        {name: 'custom_category', label: 'Category', status: constants.FIELD_HIDDEN, priority: 19}
      ],
      toValidateCount: 0,
      commentsToReviewCount: 0,
      labels: [/* string */],
      types: [/* {title, value} */],
      customCategories: [/* {title, value} */],
      feedback: [],
      statuses: {
        new: 0,
        active: {
          count: 0,
          nested: [/* {count, group} */]
        },
        closed: {
          count: 0,
          nested: [/* {count, group} */]
        },
        hidden: {
          count: 0,
          nested: [/* {count, group} */]
        }
      }
    };
  }

  toValidate(prev, {payload}) {
    const next           = {...prev};
    next.toValidateCount = payload.data.count;
    return next;
  }

  commentsToReview(prev, {payload}) {
    const next                 = {...prev};
    next.commentsToReviewCount = payload.data.count;
    return next;
  }

  labels(prev, {payload}) {
    const next  = {...prev};
    next.labels = payload.data;
    return next;
  }

  types(prev, {payload}) {
    const next = {...prev};
    next.types = payload.data;
    return next;
  }

  customCategories(prev, {payload}) {
    const next            = {...prev};
    next.customCategories = payload.data.nested;
    return next;
  }

  new(prev, {payload}) {
    const next        = {...prev};
    next.statuses.new = payload.data.count;

    return next;
  }

  active(prev, {payload}) {
    const next           = {...prev};
    next.statuses.active = payload.data;
    return next;
  }

  closed(prev, {payload}) {
    const next           = {...prev};
    next.statuses.closed = payload.data;
    return next;
  }

  hidden(prev, {payload}) {
    const next           = {...prev};
    next.statuses.hidden = payload.data;
    return next;
  }

  getList(prev, {payload}) {
    const next    = {...prev};
    next.feedback = payload.data;
    return next;
  }

  queryChanged(prev, {payload}) {
    const next = {...prev};
    next.query = payload;
    return next;
  }

  viewModeChanged(prev, {payload}) {
    const next    = {...prev};
    next.viewMode = payload.viewMode;
    return next;
  }


  orderChanged(prev, {payload}) {
    const next = {...prev};
    next.order = payload.order;
    return next;
  }

  getFilterValues(prev, {payload}) {
    const next        = {...prev};
    let values        = [];
    payload.data.map(item => values.push(item['title']));
    next.filterValues = values;
    return next;
  }

  setFilterValue(prev, {payload}) {
    const next         = {...prev};
    next.filters.alias = payload.filter;
    next.filters.value = payload.value;
    return next;
  }

  resetFilterValue(prev) {
    const next         = {...prev};
    next.filters.value = '';
    return next;
  }

  resetFilters(prev, {payload}) {
    const next   = {...prev};
    next.filters = payload;

    return next;
  }

  setTableSort(prev, {payload}) {
    const next = {...prev};
    next.sort  = payload.sort;
    next.order = payload.order;
    return next;
  }

  sortChanged(prev, {payload}) {
    const next    = {...prev};
    next.sort     = payload.sort;
    next.sortName = payload.sortName;
    return next;
  }

  displayFieldsChanged(prev, {payload}) {
    const next = {...prev};
    next[payload.type].forEach(obj=> {
      if (obj.name === payload.field) {
        obj.status = payload.status
      }
    });
    return next;
  }

  getDisplayFields(prev, {payload}) {
    const next = {...prev};
    return next;
  }


  registerHandlers() {
    this
      .r(FeedbackListActions.toggleViewMode, this.viewModeChanged)
      .r(FeedbackListActions.changeDisplayFieldsStatus, this.displayFieldsChanged)
      .r(FeedbackListActions.getDisplayFieldsFromPersonSetting, this.getDisplayFields)
      .r(FeedbackListActions.toggleOrder, this.orderChanged)
      .r(FeedbackListActions.toggleSort, this.sortChanged)
      .r(FeedbackListActions.feedbackToValidate, this.toValidate)
      .r(FeedbackListActions.feedbackLabels, this.labels)
      .r(FeedbackListActions.feedbackTypes, this.types)
      .r(FeedbackListActions.feedbackCustomCategories, this.customCategories)
      .r(FeedbackListActions.feedbackNew, this.new)
      .r(FeedbackListActions.feedbackActiveStatus, this.active)
      .r(FeedbackListActions.feedbackClosedStatus, this.closed)
      .r(FeedbackListActions.feedbackHiddenStatus, this.hidden)
      .r(FeedbackListActions.changeQueryState, this.queryChanged)
      .r(FeedbackListActions.getFilterValues, this.getFilterValues)
      .r(FeedbackListActions.setFilterValue, this.setFilterValue)
      .r(FeedbackListActions.resetFilterValue, this.resetFilterValue)
      .r(FeedbackListActions.resetFilters, this.resetFilters)
      .r(FeedbackListActions.setTableSort, this.setTableSort)
      .r(FeedbackListActions.loadFeedbackList, this.getList);
  }

}
