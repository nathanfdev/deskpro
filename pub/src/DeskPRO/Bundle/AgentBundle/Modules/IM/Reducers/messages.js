import * as actions from '../Actions/imMessagesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';
import Immutable from 'immutable';
import { async } from 'Ampliflux/reducers/handlers';

const initialState = {
  chatMessages: {}
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
      return state.updateIn(['chatMessages', payload.chat_id], messages => {
        messages.push(payload.message);
        return messages;
      });
    }
  })

});
