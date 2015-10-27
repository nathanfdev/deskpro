import * as actions from '../Actions/messagesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';
import Immutable from 'immutable';
import { async } from 'Ampliflux/reducers/handlers';

const initialState = {
  chatMessages: {},
  lastMessages: {}
};
export default createReducer(initialState, {
  [actions.loadMessages]: async(
    {
      success: (state, payload) => {
        return state.setIn(['chatMessages', payload.chat_id], payload.messages);
      }
    }
  ),
  [actions.addMessageOptimistic]: async({
    success: (state, payload) => {
      // updateIn works well enough, but not causes components rerender
      const messages = state.getIn(['chatMessages', payload.chat_id]).push(payload.message);
      return state.set(['chatMessages', payload.chat_id], messages);
    }
  }),
  [actions.refreshCounts]: (state, payload) => {
    return state.set('lastMessages', payload);
  }
});
