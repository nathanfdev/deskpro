import { createAction } from 'Ampliflux';
import * as IM from 'DeskPRO/Bundle/AgentBundle/Services/Api/IM';

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

export const addMessageOptimistic = createAction(
  'IM_CHAT_ADD_MESSAGE_OPTIMISTIC',
  (chatId, message, uuid, me) => {
    return {
      data: {
        agent_chat_id: chatId,
        date_created: null,
        timestamp: new Date().getTime() / 1000,
        id: null,
        uuid: uuid,
        message: message,
        status: 0,
        metadata: null,
        person_id: me.get('id'),
        person_name: me.get('name'),
        old: false
      }
    };
  }
);

export const addMessage = createAction(
  'IM_CHAT_ADD_MESSAGE',
  (chatId, message, uuid, me) => (dispatch) => {
    dispatch(addMessageOptimistic(chatId, message, uuid, me));
    IM.addMessage(chatId, message, uuid).then(response => {
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

export const reduceCounts = createAction(
  'IM_DECREASE_COUNTS',
  (chatId) => chatId
);


export const markMessagesOptimistic = createAction(
  'IM_MARK_MESSAGES_OPTIMISTIC',
  (uuids, chatId, status) => { return {uuids: uuids, chatId: chatId, status: status}; }
);

export const markMessages = createAction(
  'IM_MARK_MESSAGES',
  (ids, uuids, chatId, status = 2) => (dispatch) => {
    dispatch(markMessagesOptimistic(uuids, chatId, status));
    return new Promise(
      (resolve, reject) => {
        return IM.markMessages(ids, status)
          .success(() => {
            return resolve({chatId: chatId, messages: uuids, status: status});
          })
          .error(response => reject(response));
      }
    );
  }
);

