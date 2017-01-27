import Immutable from 'immutable';
import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import * as actions from '../Actions/messagesActions';
import { newActionAlerts } from '../../Application/Actions/notificationActions';
import { MessagesHelper } from '../../../Services/Helpers/MessagesHelper';

const initialState = {
  me:               {},
  chatMessages:     {},
  searchMessages:   {},
  counts:           { nested: {} },
  loadingMessages:  true,
  loadingCounts:    true,
  updatingMessages: false,
  searching:        false,
  drafts:           {}
};

export default createReducer(initialState, {
  [actions.setImMe]: (state, payload) => state.set('me', payload),

  [actions.loadMessages]: async(
    {
      start:   state => state.set('loadingMessages', true),
      success: (state, payloadArgument) => {
        const payload  = payloadArgument;
        const newState = state.set('searching', Boolean(payload.searchQuery));
        const chat     = MessagesHelper.getChat(newState, payload.chat);
        const path     = MessagesHelper.getPath(newState, payload.chat);

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

        return newState.setIn(path, { ...chat });
      },
      done: state => state.set('loadingMessages', false)
    }
  ),

  [actions.addMessageOptimistic]:   MessagesHelper.addMessageOptimistic,
  [actions.markMessagesOptimistic]: MessagesHelper.markMessagesOptimistic,

  [actions.markMessages]: async({
    success: MessagesHelper.markMessages,
    done:    state => state.set('updatingMessages', false)
  }),

  [actions.refreshCounts]: async(
    {
      success: (state, payload) => {
        DeskPRO_Window.notifications.fireEvent('modCount', { count: payload.count }); // eslint-disable-line no-undef
        return state.set('counts', payload);
      },
      done: state => state.set('loadingCounts', false)
    }
  ),
  [actions.saveDraft]:  (state, payload) => state.set('drafts', payload),
  [actions.loadDrafts]: (state, payload) => state.set('drafts', payload),

  [newActionAlerts]: MessagesHelper.handleActionAlerts
});
