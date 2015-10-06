import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import Immutable from 'immutable';

export const loadMessages = createAction(
  'IM_LOAD_MESSAGES',
  (chat_id, searchQuery = '') => {
    return IM.loadMessages(chat_id, searchQuery).then(response => {
      const messages = response.data.data;
      return new Promise((resolve) => {
        let chatMessages = {};
        chatMessages[chat_id] = messages;
        resolve(chatMessages);
      });
    });

  }
);


export const addMessage = createAction(
  'IM_CHAT_ADD_MESSAGE',
  (chat_id, message) => (dispatch) => {
    dispatch(addMessageOptimistic(chat_id, message));
    IM.addMessage(chat_id, message).then(response => {
      "use strict";
      const message = response.data.data;
      return new Promise((resolve) => {
        let chatMessages = {};
        chatMessages[chat_id] = [message];
        resolve(chatMessages);
      });
    });
  }
);


export const addMessageOptimistic = createAction(
  'IM_CHAT_ADD_MESSAGE_OPTIMISTIC',
  (chat_id, message) => {
    return new Promise((resolve) => {
      let chatMessages = {};
      chatMessages[chat_id] = [message];
      resolve(chatMessages);
    });
  }
);