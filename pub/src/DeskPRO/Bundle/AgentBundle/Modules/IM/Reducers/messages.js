import * as actions from '../Actions/imMessagesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

const initialState = {
  chatMessages: {}
};
export default createReducer(initialState, {
  [actions.loadMessages]: (state, payload) => {
    return state.set('chatMessages', payload);
  },
  [actions.addMessageOptimistic]: (state, payload) => {
    let pay = {};
    pay[payload.id] = payload.data;
    console.log(payload);
    const messages_old = state.getIn(['chatMessages', payload.id]);
    let messages_new = [payload.message];
    messages_new = messages_old.mergeDeep(messages_new);
    return state.set('chatMessages', messages_new);
  }
});
