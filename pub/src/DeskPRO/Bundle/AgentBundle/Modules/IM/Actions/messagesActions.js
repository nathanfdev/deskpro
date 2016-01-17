import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';
import { releaseChats, setChatsRequest } from '../RecordStores/Actions/chatsActions';

export const loadMessages = createAction(
  'IM_LOAD_MESSAGES',
  (chatId, searchQuery = '', page = null) => {
    return IM.loadMessages(chatId, searchQuery, page).then(response => {
      const messages = response.data.data;
      const meta = response.data.meta.pagination;
      return new Promise((resolve) => {
        resolve({chat_id: chatId, messages: messages, page: meta.current_page, pages: meta.total_pages, searchQuery: searchQuery});
      });
    });
  }
);

export const addMessage = createAction(
  'IM_CHAT_ADD_MESSAGE',
  (chatId, message) => {
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
  () => {
    return new Promise(
      (resolve, reject) => {
        return IM.loadMessagesCount()
          .success((response) => {


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