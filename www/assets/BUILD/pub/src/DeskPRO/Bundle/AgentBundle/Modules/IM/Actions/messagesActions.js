import { createAction } from 'Ampliflux';
import { api, repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const setImMe = createAction(
  'SET_IM_ME',
  me => me
);

export const loadMessages = createAction(
  'IM_LOAD_MESSAGES',
  (chatId, searchQuery = '', page = 1) =>
    repository('AgentChat')
      .loadMessages(chatId, searchQuery, page)
      .then((response) => {
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
  (chatId, message, uuid, me) => ({
    data: {
      chat:         chatId,
      date_created: new Date(),
      timestamp:    Date.now(),
      id:           null,
      uuid,
      message,
      status:       0,
      metadata:     null,
      person:       me.get('id'),
      person_name:  me.get('name'),
      old:          false
    }
  })
);

export const addMessage = createAction(
  'IM_CHAT_ADD_MESSAGE',
  (chatId, message, uuid, me, blobs) => (dispatch) => {
    dispatch(addMessageOptimistic(chatId, message, uuid, me));
    repository('AgentChat').addMessage(chatId, message, uuid, blobs).then((response) => {
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
      (resolve, reject) => repository('AgentChat').loadMessagesCount()
          .success(response => resolve(response.data))
          .error(response => reject(response))
      )
);

export const markMessagesOptimistic = createAction(
  'IM_MARK_MESSAGES_OPTIMISTIC',
  (uuids, chatId, status) => ({ uuids, chatId, status })
);

export const markAllMessagesOptimistic = createAction(
  'IM_MARK_ALL_MESSAGES_OPTIMISTIC',
  (chat) => { const obj = { chatId: chat }; return obj; }
);

export const markMessages = createAction(
  'IM_MARK_MESSAGES',
  (ids, uuids, chatId, status = 2) => dispatch => new Promise(
      (resolve, reject) => {
        dispatch(markMessagesOptimistic(uuids, chatId, status));
        return repository('AgentChat').markMessages(chatId, ids, status)
          .success(() => resolve({ chatId, uuids, status }))
          .error(response => reject(response));
      }
    )
);

export const markAllMessagesAsRead = createAction(
  'IM_MARK_ALL_MESSAGES',
  chatId => dispatch => new Promise(
    (resolve, reject) => {
      dispatch(markAllMessagesOptimistic(chatId));
      return repository('AgentChat').markAllMessagesAsRead(chatId)
        .success(() => resolve({ chatId }))
        .error(response => reject(response));
    }
  )
);

export const saveDraft = createAction(
  'IM_SAVE_DRAFT',
  (chatId, message) => {
    const drafts = localStorage.getItem('drafts') ? JSON.parse(localStorage.getItem('drafts')) : {};
    drafts[chatId] = message;
    localStorage.setItem('drafts', JSON.stringify(drafts));
    return drafts;
  }
);

export const loadDrafts = createAction(
  'IM_LOAD_DRAFTS',
  () => {
    let drafts = localStorage.getItem('drafts');
    if (drafts) {
      drafts = JSON.parse(localStorage.getItem('drafts'));
    } else {
      drafts = {};
    }

    return drafts;
  }
);

export const searchMessageClick = createAction(
  'IM_SEARCH_MESSAGE_CLICK',
  message => (dispatch) => {
    api.sendGet(`DP_API/agent_chats/${message.chat}/messages/${message.id}/page?order_by=date_created`).success((response) => {
      dispatch(loadMessages(message.chat, '', response.data));
    });
    return {};
  }
);
