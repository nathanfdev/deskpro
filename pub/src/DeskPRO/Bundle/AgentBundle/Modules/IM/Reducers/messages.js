import * as actions from '../Actions/imMessagesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';
import Immutable from 'immutable';

const initialState = {
  chatMessages: {}
};
export default createReducer(initialState, {
  [actions.loadMessages]: (state, payload) => {
    return state.set('chatMessages', payload);
  },
  [actions.addMessageOptimistic]: (state, payload) => {
    let pay = {};
    pay[payload.id] = payload.message;
    console.log(payload);
    const messages_old = Immutable.Map(state.getIn(['chatMessages', payload.agent_chat_id]));
    let messages_new = (state.getIn(['chatMessages', payload]));
    messages_new = messages_old.mergeDeep(pay);
    console.log(messages_new, messages_old);
    return state.set('chatMessages', messages_new);
  }
});
