import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';

// Chat setup actions
export const setChatId = createAction('WIDGET_CHAT_SET_ID');
export const updateChatInfo = createAction('WIDGET_CHAT_UPDATE_CHAT_INFO');

// Audio actions
export const toggleAudioNotifications = createAction('WIDGET_CHAT_TOGGLE_AUDIO_NOTIFICATIONS');

// Transcript actions
export const disableSendTranscript = createAction('WIDGET_CHAT_DISABLE_SEND_TRANSCRIPT');
export const enableSendTranscript = createAction('WIDGET_CHAT_ENABLE_SEND_TRANSCRIPT');
export const resetTranscriptDataSent = createAction('WIDGET_CHAT_RESET_TRANSCRIPT_DATA_SENT');

// Messages actions
export const resetMessages = createAction('WIDGET_CHAT_RESET_MESSAGES');
export const addNewMessage = createAction('WIDGET_CHAT_ADD_NEW_MESSAGES');

// Uploading files actions
export const addUploadingFile = createAction('WIDGET_CHAT_ADD_UPLOADING_FILE');
export const markUploadingFileFailed = createAction('WIDGET_CHAT_MARK_UPLOADING_FILE_FAILED');
export const removeUploadingFile = createAction('WIDGET_CHAT_REMOVE_UPLOADING_FILE');

// Attachment actions
export const addAttachment = createAction('WIDGET_CHAT_ADD_ATTACHMENT');
export const removeAttachment = createAction('WIDGET_CHAT_REMOVE_ATTACHMENT');
export const resetAttachments = createAction('WIDGET_CHAT_RESET_ATTACHMENTS');

// Api actions
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
        dispatch(resetTranscriptDataSent());
        dispatch(updateChatInfo(data));
      }

      if (chatId && data.author_email) {
        dispatch(enableSendTranscript());
      } else {
        dispatch(disableSendTranscript());
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
          newMessages.forEach(message => dispatch(addNewMessage(message)));
        }
      });
  }
);

export const sendChatMessage = createAction(
  'WIDGET_CHAT_SEND_MESSAGE',
  (chatId, params) => dispatch => {
    if (!chatId) {
      return null;
    }

    dispatch(resetAttachments());
    return DpApi.sendPost(`DP_API/chats/${chatId}/messages`, params);
  }
);

export const sendTranscriptInfo = createAction(
  'WIDGET_CHAT_SEND_TRANSCRIPT_INFO',
  (chatId, params) => chatId ? DpApi.sendPost(`DP_API/chats/${chatId}/transcript_info`, params) : null
);

export const sendTranscriptData = createAction(
  'WIDGET_CHAT_SEND_TRANSCRIPT_DATA',
  chatId => chatId ? DpApi.sendPost(`DP_API/chats/${chatId}/transcript_data`) : null
);

export const endChat = createAction(
  'WIDGET_CHAT_END',
  chatId => chatId ? DpApi.sendPost(`DP_API/chats/${chatId}/end`) : null
);

export const reopenChat = createAction(
  'WIDGET_CHAT_REOPEN',
  chatId => chatId ? DpApi.sendPost(`DP_API/chats/${chatId}/reopen`) : null
);

export const sendFeedback = createAction(
  'WIDGET_CHAT_SEND_FEEDBACK',
  (chatId, params) => chatId ? DpApi.sendPost(`DP_API/chats/${chatId}/feedback`, params) : null
);
