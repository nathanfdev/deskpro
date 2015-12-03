import { createReducer } from 'Ampliflux';
import { async, setValue, setFullPayload, togglePayloadInCollection, handleMassAction, mergeFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Immutable from 'immutable';

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
    success: (state, payload) => state.set('elements', payload.data).set('pagination', payload.meta.pagination),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),
  [actions.loadFeedbackCommentsList]: async({
    success: (state, payload) => state.set('elements', payload.data).set('pagination', payload.meta.pagination),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),
  [actions.toggleMassAction]: handleMassAction('elements', 'selected'),
  [actions.setMassActionsParams]: (state, payload) => state.setIn(['massActions', payload.param], payload.value),
  [actions.resetMassActionsParams]: (state) => state.set('massActions', Immutable.fromJS({})),
  [actions.toggleSelectedAction]: togglePayloadInCollection('selected'),
  [actions.toggleTableFieldVisibility]: togglePayloadInCollection('tableVisibleFields'),
  [actions.toggleCardFieldVisibility]: togglePayloadInCollection('cardVisibleFields'),
  [actions.setViewFieldsSettingStoredFlag]: (state, payload) => state.set('viewFieldsSettingsFromDb', payload),
  [actions.getDisplayFieldsFromPersonSetting]: async({
    success: mergeFullPayload()
  }),
  [actions.setParams]: setFullPayload('currentListParams'),
  [actions.setDisplayFields]: mergeFullPayload()
});
