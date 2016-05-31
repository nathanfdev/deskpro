import { createReducer } from 'DeskPRO/Component/Ampliflux';
import { async, setValue, setFullPayload } from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import * as actions from '../Actions/chatListActions';

const initialState = {
  async:             { done: true },
  viewMode:          constants.VIEW_MODE_CARD,
  currentListParams: {
    order_by:  'date_created',
    order_dir: constants.ORDER_DESC
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

  [actions.updateCurrentListParams]: setFullPayload('currentListParams')
});
