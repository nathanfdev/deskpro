import { createReducer } from 'Ampliflux';
import { async, setValue, setFullPayload, togglePayloadInCollection, handleMassAction } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  elements: [], // array of list elements (feedback or comments)
  selected: [], // array of IDs

  // currently viewed list GET parameters map
  currentListParams: {
    isComments: false,
    sort: 'date_created',
    order: constants.ORDER_DESC,
    labels_mode: 'any'
  },
  async: {
    done: true
  },
  tableVisibleFields: ['id', 'urgency', 'person', 'agent', 'subject', 'status'],
  cardVisibleFields: ['id', 'urgency', 'person', 'agent', 'subject', 'status', 'date_created', 'labels'],
  commentsTableViewFields: [ // temporary, must be removed later
    { name: 'id', label: 'ID', className: 'id-col', status: constants.FIELD_SHOWN, priority: 1 },
    { name: 'status', label: 'Status', status: constants.FIELD_SHOWN, priority: 2 },
    { name: 'date_created', label: 'Created', status: constants.FIELD_SHOWN, priority: 10 },
    { name: 'validating', label: 'Validating', status: constants.FIELD_SHOWN, priority: 16 },
    { name: 'content', label: 'Content', status: constants.FIELD_SHOWN, priority: 18 }
  ]
};

export default createReducer(initialState, {
  [actions.loadList]: async({
    success: (state, payload) => state.set('elements', payload.data),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),
  [actions.toggleMassAction]: handleMassAction('elements', 'selected'),
  [actions.toggleSelectedAction]: togglePayloadInCollection('selected'),
  [actions.toggleTableFieldVisibility]: togglePayloadInCollection('tableVisibleFields'),
  [actions.toggleCardFieldVisibility]: togglePayloadInCollection('cardVisibleFields'),
  [actions.getDisplayFieldsFromPersonSetting]: async({
    success: (state, payload) =>
      state.setIn(['viewFields'], payload.data.value)
  }),
  [actions.setParams]: setFullPayload('currentListParams')
});
