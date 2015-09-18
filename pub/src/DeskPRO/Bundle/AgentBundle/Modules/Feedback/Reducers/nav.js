import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import Immutable from "immutable";

const initialState = {
  viewModeOptions: [
    {field: constants.VIEW_MODE_TABLE, label: 'Table view', icon: 'fa-table', current: true},
    {field: constants.VIEW_MODE_LIST, label: 'List view', icon: 'fa-list', current: false}
  ],
  order: constants.ORDER_DESC, /* Asc, Desc */
  sortOptions: [
    {field: 'date_created', label: 'Date', icon: 'fa-calendar-o', current: true},
    {field: 'total_rating', label: 'Rating', icon: 'fa-calendar-o', current: false},
    {field: 'num_ratings', label: 'Number of votes', icon: 'fa-calendar-o', current: false}
  ],
  query: {awaiting_validation: 1},
  filters: {
    name: 'type',
    alias: 'category',
    value: ''
  },
  filterValues: [/* string */],
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

export default createReducer(initialState, {
  [actions.feedbackToValidate]: async({
    success: (state, payload) =>
      state.setIn(['toValidateCount'], payload.data.count)
  }),
  [actions.commentsToReview]: async({
    success: (state, payload) =>
      state.setIn(['commentsToReviewCount'], payload.data.count)
  }),
  [actions.feedbackLabels]: async({
    success: (state, payload) =>
      state.setIn(['labels'], payload.data)
  }),
  [actions.feedbackTypes]: async({
    success: (state, payload) =>
      state.setIn(['feedbackTypes'], payload.data)
  }),
  [actions.feedbackCustomCategories]: async({
    success: (state, payload) =>
      state.setIn(['feedbackCustomCategories'], payload.data.nested)
  }),
  [actions.feedbackNew]: async({
    success: (state, payload) =>
      state.setIn(['statuses', 'new'], payload.data.count)
  }),
  [actions.feedbackActiveStatus]: async({
    success: (state, payload) =>
      state
        .setIn(['statuses', 'active', 'count'], payload.data.count)
        .setIn(['statuses', 'active', 'nested'], payload.data.nested)
  }),
  [actions.feedbackClosedStatus]: async({
    success: (state, payload) =>
      state
        .setIn(['statuses', 'closed', 'count'], payload.data.count)
        .setIn(['statuses', 'closed', 'nested'], payload.data.nested)
  }),
  [actions.feedbackHiddenStatus]: async({
    success: (state, payload) =>
      state
        .setIn(['statuses', 'hidden', 'count'], payload.data.count)
        .setIn(['statuses', 'hidden', 'nested'], payload.data.nested)
  }),
  [actions.getFilterValues]: async({
    success: (state, payload) => {
      let values = [];
      payload.data.map(item => values.push(item['title']));
      state.setIn(['filterValues'], values)
    }
  }),


});
