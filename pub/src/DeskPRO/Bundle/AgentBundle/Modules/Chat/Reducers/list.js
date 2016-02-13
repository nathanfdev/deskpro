import { createReducer } from 'Ampliflux';
import { async, setValue, setFullPayload } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/chatListActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

const initialState = {
  viewMode: constants.VIEW_MODE_CARD,
  currentListParams: {
    sort: 'date_created',
    order: constants.ORDER_DESC
  }
};

export default createReducer(initialState, {
  [actions.load]: async({
    success: (state, payload) => state.set('pagination', payload.pagination)
  }),
  [actions.updateCurrentListParams]: setFullPayload('currentListParams')
});
