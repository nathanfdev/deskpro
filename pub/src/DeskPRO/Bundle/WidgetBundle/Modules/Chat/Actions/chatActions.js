import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';

export const toggleAudioNotifications = createAction('WIDGET_CHAT_TOGGLE_AUDIO_NOTIFICATIONS');
export const setChatId = createAction('WIDGET_CHAT_SET_ID');
export const updateChatInfo = createAction('WIDGET_CHAT_UPDATE_CHAT_INFO');
export const resetMessages = createAction('WIDGET_CHAT_RESET_MESSAGES');
export const addNewMessages = createAction('WIDGET_CHAT_ADD_NEW_MESSAGES');

export const createChat = createAction(
  'WIDGET_CHAT_CREATE_NEW',
  params => dispatch => DpApi
    .sendPost('DP_API/chats/create', params)
    .success(response => {
      const data = response.data || {};
      const chatId = data.id;

      if (chatId) {
        dispatch(setChatId(chatId));
        dispatch(resetMessages());
        dispatch(updateChatInfo(response.data));
      }
    })
);

export const pollingChat = createAction(
  'WIDGET_CHAT_POLLING',
  (chatId, params) => dispatch => {
    if (!chatId) {
      return null;
    }

    return DpApi
      .sendGet(`DP_API/chats/${chatId}/polling?` + compileParams(params))
      .success(response => {
        const chatInfo = response.chat_info && response.chat_info.data;
        const newMessages = response.new_messages ? response.new_messages.data : [];

        if (chatInfo) {
          dispatch(updateChatInfo(chatInfo));
        }
        if (newMessages.length) {
          dispatch(addNewMessages(newMessages));
        }
      });
  }
);

export const sendChatMessage = createAction(
  'WIDGET_CHAT_SEND_MESSAGE',
  (chatId, params) => {
    if (!chatId) {
      return null;
    }

    return DpApi.sendPost(`DP_API/chats/${chatId}/messages`, params);
  }
);

export const endChat = createAction(
  'WIDGET_CHAT_END',
  chatId => {
    if (!chatId) {
      return null;
    }

    return DpApi.sendPost(`DP_API/chats/${chatId}/end`);
  }
);

export const reopenChat = createAction(
  'WIDGET_CHAT_REOPEN',
  chatId => {
    if (!chatId) {
      return null;
    }

    return DpApi.sendPost(`DP_API/chats/${chatId}/reopen`);
  }
);

export const sendFeedback = createAction(
  'WIDGET_CHAT_SEND_FEEDBACK',
  (chatId, params) => {
    if (!chatId) {
      return null;
    }

    return DpApi.sendPost(`DP_API/chats/${chatId}/feedback`, params);
  }
);
