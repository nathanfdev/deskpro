import { createAction } from 'Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadMessages = createAction(
  'IM_LOAD_MESSAGES',
  (chatId, searchQuery = '', page = 1) =>
    repository('AgentChat')
      .loadMessages(chatId, searchQuery, page)
      .then(response => {
        const messages = response.data.data;
        const meta = response.data.meta.pagination;

        return new Promise((resolve) => {
          resolve({
            messages,
            searchQuery,
            chat:  chatId,
            page:  meta.current_page,
            pages: meta.total_pages
          });
        });
      })
);

export const addMessageOptimistic = createAction(
  'IM_CHAT_ADD_MESSAGE_OPTIMISTIC',
  (chatId, message, uuid, me) => {
    return {
      data: {
        chat:         chatId,
        date_created: new Date(),
        id:           null,
        ['uuid']:     uuid,
        ['message']:  message,
        status:       0,
        metadata:     null,
        person:       me.get('id'),
        person_name:  me.get('name'),
        old:          false
      }
    };
  }
);

export const addMessage = createAction(
  'IM_CHAT_ADD_MESSAGE',
  (chatId, message, uuid, me) => (dispatch) => {
    dispatch(addMessageOptimistic(chatId, message, uuid, me));
    repository('AgentChat').addMessage(chatId, message, uuid).then(response => {
      const responseMessage = response.data.data;
      return new Promise((resolve) => {
        resolve(responseMessage);
      });
    });
  }
);

export const refreshCounts = createAction(
  'IM_COUNT_MESSAGES',
  () => new Promise(
      (resolve, reject) => {
        return repository('AgentChat').loadMessagesCount()
          .success((response) => {
            return resolve(response.data);
          })
          .error(response => reject(response));
      }
    )
);

export const markMessagesOptimistic = createAction(
  'IM_MARK_MESSAGES_OPTIMISTIC',
  (uuids, chatId, status) => {
    return { uuids, chatId, status };
  }
);

export const markMessages = createAction(
  'IM_MARK_MESSAGES',
  (ids, uuids, chatId, status = 2) => (dispatch) => {
    dispatch(markMessagesOptimistic(uuids, chatId, status));
    return new Promise(
      (resolve, reject) => {
        return repository('AgentChat').markMessages(chatId, ids, status)
          .success(() => {
            return resolve({ chatId, uuids, status });
          })
          .error(response => reject(response));
      }
    );
  }
);

