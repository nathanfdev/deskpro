import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import { releaseChats, setChatsRequest } from '../RecordStores/Actions/chatsActions';

export const loadMessages = createAction(
  'IM_LOAD_MESSAGES',
  (chatId, searchQuery = '') => {
    return IM.loadMessages(chatId, searchQuery).then(response => {
      const messages = response.data.data;
      return new Promise((resolve) => {
        resolve({chat_id: chatId, messages: messages});
      });
    });
  }
);

export const addMessageOptimistic = createAction(
  'IM_CHAT_ADD_MESSAGE_OPTIMISTIC',
  (chatId, message, me) => {
    return new Promise((resolve) => {
      const payload = {
        chat_id: chatId,
        message: {
          agent_chat_id: chatId,
          date_created: new Date().toUTCString(),
          id: null,
          message: message,
          metadata: null,
          person_id: me.get('id'),
          person_name: me.get('name'),
          old: false
        }
      };

      resolve(payload);
    });
  }
);

export const addMessage = createAction(
  'IM_CHAT_ADD_MESSAGE',
  (chatId, message, author) => (dispatch) => {
    dispatch(addMessageOptimistic(chatId, message, author));
    IM.addMessage(chatId, message).then(response => {
      const responseMessage = response.data.data;
      return new Promise((resolve) => {
        resolve(responseMessage);
      });
    });
  }
);

export const refreshCounts = createAction(
  'IM_COUNT_MESSAGES',
  () => (dispatch) => {
    return new Promise(
      (resolve, reject) => {
        return IM.loadMessagesCount()
          .success((response) => {
            const records = {};
            const ids = [];
            Object.keys(response.data).map((key) => {
              const item = response.data[key];
              ids.push(parseInt(item.chat_id, 10));
              records[item.chat_id] = item.chat;
            });
            dispatch(releaseChats('recent', ids));
            dispatch(setChatsRequest('recent', records, ids));

            return resolve(response.data);
          })
          .error(response => reject(response));
      }
    );
  }
);

export const markMessages = createAction(
  'IM_MARK_MESSAGES',
  (ids) => {
    return new Promise(
      (resolve, reject) => {
        return IM.markMessages(ids)
          .success((response) => {
            return resolve(response.data);
          })
          .error(response => reject(response));
      }
    );
  }
);