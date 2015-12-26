import * as actions from '../Actions/messagesActions';
import { newActionAlerts } from '../../Application/Actions/notificationActions';
import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';

const initialState = {
  chatMessages: {},
  searchMessages: {},
  loadingMessages: true,
  counts: [],
  countsLoading: true
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
        payload.messages.map((message) => chat.messages.push(message));
        chat.page = Math.max(chat.page, payload.page);
        return state.setIn(path, {...chat});
      },
      done: (state) => state.set('loadingMessages', false)
    }
  ),
  [newActionAlerts]: (state, payload) => {
    let newState = state;
    Immutable.List(payload).map((element) => {
      if (element.type === 'notification.agent_chat.new_message') {
        const path = ['chatMessages', element.data.agent_chat_id];
        const chat = newState.getIn(path);
        if (chat) {
          chat.messages.push(element.data);
          newState = newState.setIn(path, {...chat});
        }
      }
    });

    return newState;
  },
  [newActionAlerts]: async({
    success: (state, payload) => {
      let newState = state;
      Immutable.List(payload).map((element) => {
        if (element.type === 'refresh_counts') {
          newState = state.set('counts', payload);
        }
      });
      return newState;
    },
    begin: (state) => state.set('countsLoading', true),
    done: (state) => state.set('countsLoading', false)
  })
});
