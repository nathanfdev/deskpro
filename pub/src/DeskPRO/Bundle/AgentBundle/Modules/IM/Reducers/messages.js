import * as actions from '../Actions/messagesActions';
import { newActionAlerts } from '../../Application/Actions/notificationActions';
import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';
import { MessagesHelper } from '../../../Services/Helpers/MessagesHelper';

const messagesHelper = new MessagesHelper();

const initialState = {
  chatMessages: {},
  searchMessages: {},
  counts: {},
  loadingMessages: true,
  loadingCounts: true,
  updatingMessages: false,
  searching: false
};

export default createReducer(initialState, {
  [actions.loadMessages]: async(
    {
      start: (state) => state.set('loadingMessages', true),
      success: (state, payload) => {
        let newState = state;
        if (payload.searchQuery) {
          newState = newState.set('searching', true);
        } else {
          newState = newState.set('searching', false);
        }

        const transformed = {chatId: payload.chat_id};
        const chat = messagesHelper.getChat(newState, transformed);

        if (!chat || (payload.searchQuery && chat.searchQuery !== payload.searchQuery)) {
          const messages = {};
          payload.messages.map((message) => {
            messages[message.uuid] = message;
          });
          payload.messages = Immutable.Map(messages);
          return newState.setIn(messagesHelper.getPath(newState, transformed), payload);
        }

        payload.messages.map((message) => chat.messages = chat.messages.set(message.uuid, message));
        chat.page = Math.max(chat.page, payload.page);
        return newState.setIn(messagesHelper.getPath(newState, transformed), {...chat});
      },
      done: (state) => state.set('loadingMessages', false)
    }
  ),
  [actions.addMessageOptimistic]: messagesHelper.addMessageOptimistic.bind(messagesHelper),
  [actions.markMessagesOptimistic]: messagesHelper.markMessagesOptimistic.bind(messagesHelper),
  [actions.markMessages]: async({
    success: messagesHelper.markMessages.bind(messagesHelper),
    done: state => state.set('updatingMessages', false)
  }),
  [actions.refreshCounts]: async(
    {
      success: (state, payload) => {
        return state.set('counts', payload);
      },
      done: (state) => state.set('loadingCounts', false)
    }
  ),
  [newActionAlerts]: (state, payload) => {
    let newState = state;
    if (payload.type === 'notification.agent_chat.new_message') {
      const path = ['chatMessages', payload.data.agent_chat_id];
      const chat = newState.getIn(path);
      if (chat) {
        chat.messages = chat.messages.set(payload.data.uuid, payload.data);
        newState = newState.setIn(path, {...chat});
      }
    } else if (payload.type === 'refresh_counts') {
      newState = newState.set('counts', payload.data);
    } else if (payload.type === 'notification.agent_chat.mark_message') {
      const transformed = {
        chatId: payload.data.chat_id,
        uuids: [payload.data.message_uuid],
        status: payload.data.status
      };
      newState = messagesHelper.markMessages(state, transformed);
    }
    return newState;
  }

});
