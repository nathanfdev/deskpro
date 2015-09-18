import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

const initialState = {
  feedback: [],
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
  ]
};

export default createReducer(initialState, {
  [actions.getFilterValues]: async({
    success: (state, payload) => {
      let values = [];
      payload.data.map(item => values.push(item['title']));
      state.setIn(['filterValues'], values)
    }
  }),
});