import * as actions from '../../Actions/imMessagesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

const initialState = {
  messages: {}
};
export default createReducer(initialState, {
  [actions.loadMessages]: (state, payload) => {
    return state.set('messages', payload);
  }
});
