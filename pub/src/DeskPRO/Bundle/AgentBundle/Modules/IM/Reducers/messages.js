import * as actions from '../Actions/imMessagesActions';
import { createReducer } from 'Ampliflux';
import { createEmptyRecordStoreState, buildRecordStoreHandlers } from 'Ampliflux/common/record-store/handlers';

export default createReducer(
  createEmptyRecordStoreState(),
  buildRecordStoreHandlers({
    gcAction: actions.gcMessages,
    releaseRecordsAction: actions.releaseMessages,
    releaseRequestAction: actions.releaseMessageRequest,
    setRequestRecordAction: actions.setMessagesRequest,
    requestRecordsAction: actions.loadRecentMessages
  })
);

/*
import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/imMessagesActions';

const initialState = {
  messages: [],
};

export default createReducer(initialState, {
  [actions.loadRecentMessages]: (state, payload) => {
    return state.set('messages', payload);
  },
  [actions.loadMessages]: (state, payload) => {
    messages = state.get('messages');
    messages.concat(payload);
    return state.set('messages', messages);
  },
  [actions.addMessage]: (state, payload) => {
    messages = state.get('messages');
    messages.push(payload);
    state.set('messages', messages);
  },
  [actions.searchInChat]: (state, payload) => {
    state.set('messages', payload);
  }
});*/
