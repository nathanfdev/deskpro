import { createReducer } from 'Ampliflux';
import { async, setValue, setFullPayload, togglePayloadInCollection, handleMassAction, mergeFullPayload }
  from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as commentsActions from '../Actions/FeedbackCommentsActions';
import * as massActions from '../Actions/FeedbackMassActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Immutable from 'immutable';

const initialState = {
  async: {
    done: true
  },
  elements: [], // array of filtered elements IDs (feedback or comments)
  selected: [], // array of IDs
  visibleFields: {
    card: [],
    table: []
  },
  // currently viewed list GET parameters map
  currentListParams: {
    isComments: false,
    sort: 'date_created',
    order: constants.ORDER_DESC,
    labels_mode: 'any'
  }
};

export default createReducer(initialState, {
  [commentsActions.loadFeedbackCommentsList]: async({
    success: (state, payload) => state.set('elements', payload.ids).set('pagination', payload.pagination),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),

  [massActions.toggleMassAction]: handleMassAction('elements', 'selected'),
  [massActions.setMassActionsParams]: mergeFullPayload('massActions'),
  [massActions.resetMassActionsParam]: (state, payload) => state.deleteIn(['massActions', payload]),
  [massActions.resetAllMassActionsParams]: (state) => state.set('massActions', Immutable.fromJS({})),
  [massActions.toggleSelectedAction]: togglePayloadInCollection('selected'),

  [actions.setParams]: setFullPayload('currentListParams'),
  [actions.loadFeedbackList]: async({
    success: (state, payload) => state.set('elements', payload.ids).set('pagination', payload.pagination),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),
  [actions.setDisplayFields]: mergeFullPayload(),
  [actions.toggleTableFieldVisibility]: togglePayloadInCollection(['visibleFields', 'table']),
  [actions.toggleCardFieldVisibility]: togglePayloadInCollection(['visibleFields', 'card']),
  [actions.setViewFieldsSettingStoredFlag]: (state, payload) => state.setIn(['visibleFields', 'fromDb'], payload),
  [actions.getDisplayFieldsFromPersonSetting]: async({
    success: mergeFullPayload()
  })
});
