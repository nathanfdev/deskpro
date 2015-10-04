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
  (chat_id, message) => {
    return {
      author: {
        gravatar_url: 'http://www.gravatar.com/avatar/85c81137eeb71564a77a337bc44d5173?&d=mm'
      },
      text: 'test'
    };
  }
);

export const searchInChat = createAction(
  'IM_CHAT_SEARCH_IN_CHAT',
  () => {
  }
);