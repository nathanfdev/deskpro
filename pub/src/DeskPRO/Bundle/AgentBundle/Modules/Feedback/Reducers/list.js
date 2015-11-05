import { createReducer } from 'Ampliflux';
import { async, setFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as commentsActions from '../Actions/FeedbackCommentsActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Immutable from 'immutable';

const initialState = {
  massAction: false,
  feedback: [],
  selected: [], // array of IDs
  comments: [],

  // currently viewed list GET parameters map
  currentListParams: {
    isComments: false, // whether comments or feedback list is shown
    sort: 'date_created',
    order: constants.ORDER_DESC
  },

  commentsTableViewFields: [ // temporary, must be removed later
    { name: 'id', label: 'ID', className: 'id-col', status: constants.FIELD_SHOWN, priority: 1 },
    { name: 'status', label: 'Status', status: constants.FIELD_SHOWN, priority: 2 },
    { name: 'date_created', label: 'Created', status: constants.FIELD_SHOWN, priority: 10 },
    { name: 'validating', label: 'Validating', status: constants.FIELD_SHOWN, priority: 16 },
    { name: 'content', label: 'Content', status: constants.FIELD_SHOWN, priority: 18 }
  ]
};

export default createReducer(initialState, {

  [actions.loadFeedbackList]: async({
    success: (state, payload) => state.set('feedback', payload.data)
  }),

  [actions.setCurrentListParams]: (state, payload) => state.set('currentListParams', Immutable.fromJS(payload)),

  [actions.setLabelsFilterMode]: (state, payload) => state.setIn(['currentListParams', 'filters', 'labels', 'mode'], payload),

  [actions.selectLabel]: (state, payload) => {
    let selected = [];
    const filtersState = state.get('currentListParams').get('filters');
    if (filtersState && filtersState.get('labels') && filtersState.get('labels').get('selected_labels')) {
      selected = filtersState.get('labels').get('selected_labels').toJS();
      if (selected.indexOf(payload) === -1) {
        selected.push(payload);
      }
    } else {
      selected.push(payload);
    }
    return state.setIn(['currentListParams', 'filters', 'labels', 'selected_labels'], Immutable.fromJS(selected));
  },

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

  [actions.getDisplayFieldsFromPersonSetting]: async({
    success: (state, payload) =>
      state.setIn(['viewFields'], payload.data.value)
  }),

  [actions.setFilterValue]: (state, payload) => {
    let newFilters = {};
    if (state.get('currentListParams').get('filters')) {
      newFilters = state.get('currentListParams').get('filters').toJS();
    }
    newFilters[payload.filter] = payload.value;
    return state.setIn(['currentListParams', 'filters'], Immutable.fromJS(newFilters));
  },

  [commentsActions.commentsToggleOrder]: setFullPayload('order'),

  [commentsActions.setTableSort]: (state, payload) => {
    const commentsTableViewFields = [];
    state.get('commentsTableViewFields').toJS().forEach(obj=> {
      const nextObj = { ...obj };
      nextObj.order = nextObj.name === payload.sort ? payload.order : false;
      commentsTableViewFields.push(nextObj);
    });
    return state.set('commentsTableViewFields', Immutable.fromJS(commentsTableViewFields));
  }
});
