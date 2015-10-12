import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import Immutable from 'immutable';

export const loadMessages = createAction(
  'IM_LOAD_MESSAGES',
  (chat_id, searchQuery = '') => {
    return IM.loadMessages(chat_id, searchQuery).then(response => {
      const messages = response.data.data;
      return new Promise((resolve) => {
        resolve({chat_id: chat_id, messages: messages});
      });
    });

  }
);


export const addMessage = createAction(
  'IM_CHAT_ADD_MESSAGE',
  (chat_id, message, author) => (dispatch) => {
    dispatch(addMessageOptimistic(chat_id, message, author));
    IM.addMessage(chat_id, message).then(response => {
      const message = response.data.data;
      return new Promise((resolve) => {
        //dispatch(loadMessages(chat_id));
        resolve(message);
      });
    });
  }
);


export const addMessageOptimistic = createAction(
  'IM_CHAT_ADD_MESSAGE_OPTIMISTIC',
  (chat_id, message, me) => {
    return new Promise((resolve) => {
      const payload = {
        chat_id: chat_id,
        message: {
          agent_chat_id: chat_id,
          date_created: new Date().toUTCString(),
          id: null,
          message: message,
          metadata: null,
          person_id: me.get('id'),
          person_name: me.get('name')
        }
      };

      resolve(payload);
    });
  }
);