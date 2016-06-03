import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { async, setValue, setFullPayload, mergeFullPayload }
  from '../../../../../Component/Ampliflux/reducers/handlers';
import * as actions from '../Actions/FeedbackListActions';
import * as commentsActions from '../Actions/FeedbackCommentsActions';
import { constants } from '../../../../AgentBundle/Constants/Constants';

export const feedbackListInitialState = {
  async:         { done: true },
  elements:      [], // array of filtered elements IDs (feedback or comments)

  fields: {
    [constants.VIEW_MODE_CARD]: [
      { id: 'category', title: 'Category', visible: true },
      { id: 'date_created', title: 'Date Created', visible: true },
      { id: 'labels', title: 'Labels', visible: true }
    ],
    [constants.VIEW_MODE_TABLE]: [
      { id: 'id', title: 'Id', visible: true },
      { id: 'title', title: 'Title', visible: true },
      { id: 'person', title: 'Author', visible: true },
      { id: 'content', title: 'Content', visible: true },
      { id: 'status', title: 'Status', visible: true },
      { id: 'date_created', title: 'Date Created', visible: true },
      { id: 'labels', title: 'Labels', visible: true }
    ]
  },

  currentListParams: { // currently viewed list GET parameters map
    isComments:  false,
    order_by:    'date_created',
    order_dir:   constants.ORDER_DESC,
    labels_mode: 'any'
  }
};

export default createReducer(feedbackListInitialState, {
  [commentsActions.loadFeedbackCommentsList]: async(
    {
      success: (state, payload) =>
                 state.set('elements', payload.ids).set('pagination', Immutable.fromJS(payload.pagination)),

      start: setValue('async.done', false),
      done:  setValue('async.done', true)
    }),

  [actions.setParams]: setFullPayload('currentListParams'),

  [actions.loadIndicator]: setValue('async.done', false),

  [actions.loadFeedbackList]: async(
    {
      success: (state, payload) => state
        .set('elements', payload.ids)
        .set('pagination', Immutable.fromJS(payload.pagination)),

      start: setValue('async.done', false),
      done:  setValue('async.done', true)
    }),

  [actions.setDisplayFields]: mergeFullPayload(),

  [actions.setViewFieldsSettingStoredFlag]: (state, payload) => state.setIn(['visibleFields', 'fromDb'], payload),

  [actions.getDisplayFieldsFromPersonSetting]: async({ success: mergeFullPayload() }),

  [actions.toggleFieldVisibility]: (state, { type, index }) => {
    const old = state.getIn(['fields', type, index, 'visible']);
    if (undefined === old) return state;
    return state.setIn(['fields', type, index, 'visible'], !old);
  },
  [actions.changeFieldOrder]: (state, { type, from, to }) => {
    const fromField = state.getIn(['fields', type, from]);
    const toField = state.getIn(['fields', type, to]);
    if (undefined === fromField || undefined === toField) return state;
    return state
    .setIn(['fields', type, from], toField)
    .setIn(['fields', type, to], fromField);
  }
});
