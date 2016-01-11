import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { async, setValue, setFullPayload, handleMassAction } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/chatListActions';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import * as massActions from '../Actions/chatMassActions';

const initialState = {
  async: {
    done: true
  },
  viewMode: constants.VIEW_MODE_CARD,
  currentListParams: {
    sort: 'date_created',
    order: constants.ORDER_DESC
  },
  elements: []
};

export default createReducer(initialState, {

  [actions.load]: async({
    success: (state, payload) => state.set('elements', payload.ids).set('pagination', payload.pagination),
    start: setValue('async.done', false),
    done: setValue('async.done', true)
  }),
  [actions.reLoad]: async({ success: setFullPayload('elements') }),

  [actions.updateCurrentListParams]: setFullPayload('currentListParams'),

  [massActions.toggleMassAction]: handleMassAction('elements', 'selected')
});
