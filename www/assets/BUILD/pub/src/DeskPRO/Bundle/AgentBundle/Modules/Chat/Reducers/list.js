import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { async, setValue, setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import { constants } from '../../../Constants/Constants';
import * as actions from '../Actions/chatListActions';

const initialState = {
  async:             { done: true },
  viewMode:          constants.VIEW_MODE_CARD,
  currentListParams: {
    order_by:  'date_created',
    order_dir: constants.ORDER_DESC
  },
  elements:          [],
  selected:          [],
  fields:            {
    [constants.VIEW_MODE_CARD]: [
      { id: 'id', title: 'ID', visible: true },
      { id: 'urgency', title: 'Urgency', visible: true },
      { id: 'person', title: 'Person', visible: true },
      { id: 'agent', title: 'Agent', visible: true },
      { id: 'subject', title: 'Subject', visible: true },
      { id: 'status', title: 'Status', visible: true },
      { id: 'date_created', title: 'Date Created', visible: true },
      { id: 'labels', title: 'Labels', visible: true }
    ],
    [constants.VIEW_MODE_TABLE]: [
      { id: 'id', title: 'ID', visible: true },
      { id: 'subject', title: 'Subject', visible: true },
      { id: 'urgency', title: 'Urgency', visible: true },
      { id: 'person', title: 'Person', visible: true },
      { id: 'date_created', title: 'Date Created', visible: true },
      { id: 'labels', title: 'Labels', visible: true }
    ]
  }
};

export default createReducer(initialState, {
  [actions.load]: async(
    {
      success: (state, payload) => state.set('pagination', payload.pagination),
      start:   setValue('async.done', false),
      done:    setValue('async.done', true)
    }
  ),

  [actions.updateCurrentListParams]: setFullPayload('currentListParams'),

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
