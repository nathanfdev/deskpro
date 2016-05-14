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
        const newState = state.set('searching', Boolean(payload.searchQuery));
        const chat = messagesHelper.getChat(newState, payload.chat_id);
        const path = messagesHelper.getPath(newState, payload.chat_id);

        if (!chat || (payload.searchQuery && chat.searchQuery !== payload.searchQuery)) {
          const messages = {};
          payload.messages.map((message) => {
            messages[message.uuid] = message;
            return messages;
          });
          payload.messages = Immutable.Map(messages);
          return newState.setIn(path, payload);
        }

        payload.messages.map((message) => {
          chat.messages = chat.messages.set(message.uuid, message);
          return chat.messages;
        });
        chat.page = Math.max(chat.page, payload.page);

        return newState.setIn(path, {...chat});
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
  [newActionAlerts]: messagesHelper.handleActionAlerts.bind(messagesHelper)
});
