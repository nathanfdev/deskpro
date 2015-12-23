import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';
import { ajaxOptions } from '../../Application/Actions/bootstrapActions';
import { isEndedSelector, messageIdsSelector, chatInfoSelector, attachmentsSelector } from '../Selectors/chat';
import { generate } from 'randomstring';
import moment from 'moment';

// Phrase translations
export const setPhraseTranslations = createAction('WIDGET_CHAT_SET_PHRASE_TRANSLATIONS');

// Chat setup actions
export const setChatId = createAction(
  'WIDGET_CHAT_SET_ID',
  chatId => {
    localStorage.setItem('dpWidget.chat.chatId', chatId);
    return chatId;
  }
);
export const setLoaded = createAction('WIDGET_CHAT_SET_LOADED');
export const updateChatInfo = createAction('WIDGET_CHAT_UPDATE_CHAT_INFO');

// Audio actions
export const toggleMute = createAction('WIDGET_CHAT_TOGGLE_MUTE');

// Transcript actions
export const disableSendTranscript = createAction('WIDGET_CHAT_DISABLE_SEND_TRANSCRIPT');
export const enableSendTranscript = createAction('WIDGET_CHAT_ENABLE_SEND_TRANSCRIPT');
export const resetTranscriptDataSent = createAction('WIDGET_CHAT_RESET_TRANSCRIPT_DATA_SENT');

// Messages actions
export const resetMessages = createAction('WIDGET_CHAT_RESET_MESSAGES');
export const addNewMessages = createAction('WIDGET_CHAT_ADD_NEW_MESSAGES');
export const markNotDelivered = createAction('WIDGET_CHAT_MARK_NOT_DELIVERED');

// Uploading files actions
export const addUploadingFile = createAction('WIDGET_CHAT_ADD_UPLOADING_FILE');
export const markUploadingFileFailed = createAction('WIDGET_CHAT_MARK_UPLOADING_FILE_FAILED');
export const repeatUploadingFile = createAction('WIDGET_CHAT_REPEAT_UPLOADING_FILE');
export const removeUploadingFile = createAction('WIDGET_CHAT_REMOVE_UPLOADING_FILE');

// Attachment actions
export const addAttachment = createAction('WIDGET_CHAT_ADD_ATTACHMENT');
export const removeAttachment = createAction('WIDGET_CHAT_REMOVE_ATTACHMENT');
export const resetAttachments = createAction('WIDGET_CHAT_RESET_ATTACHMENTS');

// Api actions
export const loadPhraseTranslations = createAction(
  'WIDGET_CHAT_LOAD_PHRASE_TRANSLATIONS',
  () => dispatch => DpApi
    .sendGet('DP_API/lang/widget-chat-phrases.json', {...ajaxOptions})
    .success(response => {
      dispatch(setPhraseTranslations(response));
    })
);

export const createChat = createAction(
  'WIDGET_CHAT_CREATE_NEW',
  params => dispatch => DpApi
    .sendPost('DP_API/chats/create', params, {...ajaxOptions})
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
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    return DpApi
      .sendGet(`DP_API/chats/${chatId}/polling?` + compileParams(params), {...ajaxOptions})
      .success(response => {
        const state = getState();

        const oldChatInfo = chatInfoSelector(state);
        const newChatInfo = response.chat_info && response.chat_info.data;
        const newMessages = response.new_messages ? response.new_messages.data : [];
        const initialLoad = !oldChatInfo.size;

        if (newChatInfo) {
          dispatch(updateChatInfo(newChatInfo));
        }
        if (newMessages.length) {
          const existMessageIds = messageIdsSelector(state);
          const filteredMessages = newMessages
            // skip user's messages because they are added optimistically,
            // but do load user's messages on initial polling request
            .filter(message => initialLoad || (!initialLoad && message.author_type !== 'user'))
            // check for unique ids
            .filter(message => existMessageIds.indexOf(message.id) === -1);

          dispatch(addNewMessages(filteredMessages));
        }
        if (initialLoad) {
          dispatch(setLoaded());
        }
      });
  }
);

export const sendChatMessage = createAction(
  'WIDGET_CHAT_SEND_MESSAGE',
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const tmpId = generate({
      length: 20,
      charset: 'alphabetic'
    });

    // add optimistic message
    dispatch(addNewMessages({
      tmp_id: tmpId,
      content: params.message,
      is_html: true,
      author_type: 'user',
      date_created: moment().format()
    }));

    // add optimistic attachments
    const state = getState();
    const attachments = attachmentsSelector(state);

    params.attachments.forEach(blobAuthId => {
      const attachment = attachments.filter(blob => blob.get('blob_auth_id') === blobAuthId).first();
      dispatch(addNewMessages({
        tmp_id: tmpId,
        content: null,
        is_html: true,
        author_type: 'user',
        date_created: moment().format(),
        metadata: {
          type: 'file',
          blob_id: attachment.get('blob_id'),
          blob: attachment
        }
      }));
    });

    // reset attachments after send
    dispatch(resetAttachments());

    const promise = DpApi.sendPost(`DP_API/chats/${chatId}/messages`, params, {...ajaxOptions});
    promise.catch(() => {
      dispatch(markNotDelivered(tmpId));
    });

    return promise;
  }
);

export const sendTranscriptData = createAction(
  'WIDGET_CHAT_SEND_TRANSCRIPT_DATA',
  chatId => chatId ? DpApi.sendPost(`DP_API/chats/${chatId}/transcript_data`, {...ajaxOptions}) : null
);

export const sendTranscriptInfo = createAction(
  'WIDGET_CHAT_SEND_TRANSCRIPT_INFO',
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const chatEnded = isEndedSelector(state);

    const promise = DpApi.sendPost(`DP_API/chats/${chatId}/transcript_info`, params, {...ajaxOptions});
    promise.success(() => {
      if (chatEnded) {
        dispatch(sendTranscriptData(chatId));
      }
    });

    return promise;
  }
);

export const endChat = createAction(
  'WIDGET_CHAT_END',
  chatId => chatId ? DpApi.sendPost(`DP_API/chats/${chatId}/end`, {...ajaxOptions}) : null
);

export const reopenChat = createAction(
  'WIDGET_CHAT_REOPEN',
  chatId => chatId ? DpApi.sendPost(`DP_API/chats/${chatId}/reopen`, {...ajaxOptions}) : null
);

export const sendFeedback = createAction(
  'WIDGET_CHAT_SEND_FEEDBACK',
  (chatId, params) => chatId ? DpApi.sendPost(`DP_API/chats/${chatId}/feedback`, params, {...ajaxOptions}) : null
);
