import { createReducer } from 'Ampliflux';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { setValue, setFullPayload, togglePayloadInCollection } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/listActions';

export const ticketsListInitialState = {
  async:      { done: true },
  viewMode:   'card',
  listParams: {
    order_by:  'urgency',
    order_dir: 'desc',
    filter:    null
  },
  pagination: {},
  elements:   [], // array of filtered tickets IDs

  fields: {
    [constants.VIEW_MODE_CARD]: [
      { id: 'id', title: 'ID', visible: true },
      { id: 'urgency', title: 'Urgency', visible: true },
      { id: 'date_created', title: 'Date Created', visible: true },
      { id: 'labels', title: 'Labels', visible: true }
    ],
    [constants.VIEW_MODE_TABLE]: [
      { id: 'id', title: 'ID', visible: true },
      { id: 'urgency', title: 'Urgency', visible: true },
      { id: 'person', title: 'Person', visible: true },
      { id: 'agent', title: 'Agent', visible: true },
      { id: 'subject', title: 'Subject', visible: true },
      { id: 'status', title: 'Status', visible: true },
      { id: 'date_created', title: 'Date Created', visible: true },
      { id: 'labels', title: 'Labels', visible: true }
    ]
  }
};

export default createReducer(ticketsListInitialState, {

  // Private -----------------------------------------------------------------------------------------------------------

  TICKETS_LIST_SET_LIST_PARAMS: setFullPayload('listParams'),
  TICKETS_LIST_SET_PAGINATION:  setFullPayload('pagination'),
  TICKETS_LIST_SET_ELEMENTS:    setFullPayload('elements'),

  // Public (control bar) ----------------------------------------------------------------------------------------------

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
  },
  [actions.setViewMode]:   setFullPayload('viewMode'),
  [actions.loadIndicator]: setValue('async.done', false)
});
