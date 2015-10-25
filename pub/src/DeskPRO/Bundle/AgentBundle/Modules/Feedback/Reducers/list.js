import { createReducer } from 'Ampliflux';
import { async, setFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as commentsActions from '../Actions/FeedbackCommentsActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Immutable from 'immutable';

const initialState = {
  isComments: false, // whether comments or feedbacks list is shown
  massAction: false,
  feedback: [],
  selected: [], // array of IDs
  comments: [],

  // currently viewed list GET parameters map
  currentListParams: {
    sort: 'date_created',
    order: constants.ORDER_DESC
  },

  filterOptions: [
    {field: 'category', label: 'Type', icon: 'fa-calendar-o', value: '', current: true},
    {field: 'status', label: 'Status', icon: 'fa-calendar-o', value: '', current: false},
    {field: 'custom_category', label: 'Category', icon: 'fa-calendar-o', value: '', current: false}
  ],
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
    {name: 'type', label: 'Type', status: constants.FIELD_HIDDEN, priority: 8},
    {name: 'date_created', label: 'Created', status: constants.FIELD_SHOWN, priority: 10},
    {name: 'total_rating', label: 'Rating', status: constants.FIELD_SHOWN, priority: 13},
    {name: 'num_rating', label: 'Votes', status: constants.FIELD_SHOWN, priority: 14},
    {name: 'num_comments', label: 'Comments', status: constants.FIELD_SHOWN, priority: 15},
    {name: 'validating', label: 'Validating', status: constants.FIELD_SHOWN, priority: 16},
    {name: 'popularity', label: 'Popularity', status: constants.FIELD_SHOWN, priority: 17},
    {name: 'content', label: 'Content', status: constants.FIELD_SHOWN, priority: 18},
    {name: 'custom_category', label: 'Category', status: constants.FIELD_HIDDEN, priority: 19}
  ],
  commentsTableViewFields: [
    {name: 'id', label: 'ID', className: 'id-col', status: constants.FIELD_SHOWN, priority: 1},
    {name: 'status', label: 'Status', status: constants.FIELD_SHOWN, priority: 2},
    {name: 'date_created', label: 'Created', status: constants.FIELD_SHOWN, priority: 10},
    {name: 'validating', label: 'Validating', status: constants.FIELD_SHOWN, priority: 16},
    {name: 'content', label: 'Content', status: constants.FIELD_SHOWN, priority: 18}
  ]
};

export default createReducer(initialState, {

  [actions.getFilterValues]: async({
    success: (state, payload) => {
      const values = [];
      payload.data.map(item => values.push(item.title));
      return state.setIn(['filterValues'], values);
    }
  }),

  [actions.loadFeedbackList]: async({
    success: (state, payload) => state.set('feedback', payload.data)
  }),

  [actions.setCurrentListParams]: (state, payload) => state.set('currentListParams', Immutable.fromJS(payload)),

  [commentsActions.loadCommentsList]: async({
    success: (state, payload) => state.set('comments', payload.data)
  }),

  [actions.toggleMassAction]: (state) => {
    let next = state.set('massAction', !state.get('massAction'));
    let selected = next.get('selected');

    if (next.get('massAction')) {
      const elements = next.get('feedback');
      elements.forEach((element) => {
        if (!selected.includes(element.id)) {
          selected = selected.push(element.id);
        }
      });
    } else {
      selected = selected.clear();
    }

    next = next.set('selected', selected);

    return next;
  },
  [actions.toggleSelectedAction]: (state, payload) => {
    let selected = state.get('selected');
    selected = selected.includes(payload)
      ? selected.delete(selected.indexOf(payload))
      : selected.push(payload);

    return state.set('selected', selected);
  },
  [actions.setTableSort]: (state, payload) => {
    const tableViewFields = [];
    state.get('tableViewFields').toJS().forEach(obj=> {
      const nextObj = {...obj};
      nextObj.order = nextObj.name === payload.sort ? payload.order : false;
      tableViewFields.push(nextObj);
    });
    return state.set('tableViewFields', Immutable.fromJS(tableViewFields));
  },
  [actions.getDisplayFieldsFromPersonSetting]: async({
    success: (state, payload) =>
      state.setIn(['viewFields'], payload.data.value)
  }),
  [commentsActions.commentsToggleOrder]: setFullPayload('order'),
  [commentsActions.setTableSort]: (state, payload) => {
    const commentsTableViewFields = [];
    state.get('commentsTableViewFields').toJS().forEach(obj=> {
      const nextObj = {...obj};
      nextObj.order = nextObj.name === payload.sort ? payload.order : false;
      commentsTableViewFields.push(nextObj);
    });
    return state.set('commentsTableViewFields', Immutable.fromJS(commentsTableViewFields));
  }
});
