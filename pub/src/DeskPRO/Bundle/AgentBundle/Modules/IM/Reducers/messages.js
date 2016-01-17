import * as actions from '../Actions/messagesActions';
import { newActionAlerts } from '../../Application/Actions/notificationActions';
import { refreshCounts } from '../Actions/messagesActions';
import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';

const initialState = {
  chatMessages: {},
  searchMessages: {},
  loadingMessages: true,
  counts: {},
  loadingCounts: true
};

export default createReducer(initialState, {
  [actions.loadMessages]: async(
    {
      start: (state) => state.set('loadingMessages', true),
      success: (state, payload) => {
        let path;
        if (payload.searchQuery) {
          path = ['searchMessages', payload.chat_id];
        } else {
          path = ['chatMessages', payload.chat_id];
        }
        if (!state.getIn(path) || (payload.searchQuery && state.getIn(path).searchQuery !== payload.searchQuery)) {
          return state.setIn(path, payload);
        }

        const chat = state.getIn(path);
        let union = {};
        chat.messages.map((message) => union[message.id] = message);
        payload.messages.map((message) => union[message.id] = message);
        union = Immutable.Map(union);
        chat.messages = union.toArray();
        chat.page = Math.max(chat.page, payload.page);
        return state.setIn(path, {...chat});
      },
      done: (state) => state.set('loadingMessages', false)
    }
  ),
  [newActionAlerts]: (state, payload) => {
    let newState = state;
    if (payload.type === 'notification.agent_chat.new_message') {
      const path = ['chatMessages', payload.data.agent_chat_id];
      const chat = newState.getIn(path);
      if (chat) {
        chat.messages.push(payload.data);
        newState = newState.setIn(path, {...chat});
      }
    } else if (payload.type === 'refresh_counts') {
      newState = newState.set('counts', payload.data);
    }
    return newState;
  },
  [refreshCounts]: async(
    {
      start: (state) => state.set('loadingCounts', true),
      success: (state, payload) => {
        return state.set('counts', payload);
      },
      done: (state) => state.set('loadingCounts', false)
    }
  )
});
