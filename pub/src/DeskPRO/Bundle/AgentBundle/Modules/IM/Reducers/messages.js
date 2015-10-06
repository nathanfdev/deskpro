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
    return state.mergeDeep({chatMessages: payload});
  }
});
