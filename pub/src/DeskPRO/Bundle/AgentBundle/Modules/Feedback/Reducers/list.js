import { createReducer } from 'Ampliflux';
import { async, setValue, setFullPayload, togglePayloadInCollection, mergeFullPayload }
  from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as commentsActions from '../Actions/FeedbackCommentsActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  async: {
    done: true
  },
  elements: [], // array of filtered elements IDs (feedback or comments)
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

  [actions.setParams]: setFullPayload('currentListParams'),
  [actions.loadIndicator]: setValue('async.done', false),
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
