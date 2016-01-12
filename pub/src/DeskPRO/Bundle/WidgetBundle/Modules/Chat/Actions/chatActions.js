import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';
import { ajaxOptions } from '../../Application/Actions/bootstrapActions';
import { addSessionCode } from '../../Application/Actions/bootstrapActions';
import { generate } from 'randomstring';
import striptags from 'striptags';
import moment from 'moment';
import Immutable from 'immutable';
import {
  skippedPollingSelector,
  lockedPollingSelector,
  chatInfoSelector,
  chatLoadedSelector,
  isEndedSelector,
  authorAvatarSelector,
  messageIdsSelector,
  attachmentsSelector,
  canReopenSelector,
  transcriptCheckedSelector,
  transcriptSendingSelector,
  transcriptSentSelector,
} from '../Selectors/chat';

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

export const unsetChatId = createAction(
  'WIDGET_CHAT_UNSET_ID',
  () => localStorage.removeItem('dpWidget.chat.chatId')
);

export const setLoaded = createAction('WIDGET_CHAT_SET_LOADED');
export const unsetLoaded = createAction('WIDGET_CHAT_UNSET_LOADED');
export const updateChatInfo = createAction('WIDGET_CHAT_UPDATE_CHAT_INFO');
export const enableChatReopen = createAction('WIDGET_CHAT_ENABLE_REOPEN');
export const disableChatReopen = createAction('WIDGET_CHAT_DISABLE_REOPEN');

// Polling actions
export const lockPollingResponse = createAction('WIDGET_LOCK_POLLING_RESPONSE');
export const unlockPollingResponse = createAction('WIDGET_UNLOCK_POLLING_RESPONSE');
export const enablePollingResponse = createAction('WIDGET_ENABLE_POLLING_RESPONSE');

// Audio actions
export const toggleMute = createAction('WIDGET_CHAT_TOGGLE_MUTE');

// Transcript actions
export const disableSendTranscript = createAction('WIDGET_CHAT_DISABLE_SEND_TRANSCRIPT');
export const enableSendTranscript = createAction('WIDGET_CHAT_ENABLE_SEND_TRANSCRIPT');

// Messages actions
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

// Feedback actions
export const showNotHelpfulForm = createAction('WIDGET_CHAT_SHOW_NOT_HELPFUL_FORM');

// Api actions
export const loadChatPhraseTranslations = createAction(
  'WIDGET_CHAT_LOAD_PHRASE_TRANSLATIONS',
  () => dispatch => DpApi
    .sendGet('DP_API/lang/widget-chat-phrases.json', {...ajaxOptions})
    .success(response => {
      dispatch(setPhraseTranslations(response));
    })
);

export const createChat = createAction(
  'WIDGET_CHAT_CREATE_NEW',
  params => (dispatch, getState) => {
    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    return DpApi
      .sendPost(`DP_API/chats/create?${queryParams}`, params, {...ajaxOptions})
      .success(response => {
        const data = response.data || {};
        const chatId = data.id;

        if (chatId) {
          dispatch(setChatId(chatId));
        }
      });
  }
);

export const validateEmail = createAction(
  'WIDGET_CHAT_VALIDATE_EMAIL',
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    return DpApi.sendPost(`DP_API/chats/${chatId}/validate/email?${queryParams}`, params, {...ajaxOptions});
  }
);

export const regenerateEmailValidationCode = createAction(
  'WIDGET_CHAT_REGENERATE_EMAIL_CODE',
  chatId => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    return DpApi.sendPost(`DP_API/chats/${chatId}/validate/email/regenerate?${queryParams}`, null, {...ajaxOptions});
  }
);

export const ackChatMessages = createAction(
  'WIDGET_CHAT_ACK_MESSAGES',
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    return DpApi.sendPost(`DP_API/chats/${chatId}/ack_messages?${queryParams}`, params, {...ajaxOptions});
  }
);

export const sendTranscriptData = createAction(
  'WIDGET_CHAT_SEND_TRANSCRIPT_DATA',
  chatId => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    return DpApi.sendPost(`DP_API/chats/${chatId}/transcript_data?${queryParams}`, null, {...ajaxOptions});
  }
);

export const sendTranscriptInfo = createAction(
  'WIDGET_CHAT_SEND_TRANSCRIPT_INFO',
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const chatEnded = isEndedSelector(state);
    const queryParams = compileParams(addSessionCode(state));

    const promise = DpApi.sendPost(`DP_API/chats/${chatId}/transcript_info?${queryParams}`, params, {...ajaxOptions});
    promise.success(() => {
      if (chatEnded) {
        dispatch(sendTranscriptData(chatId));
      }
    });

    return promise;
  }
);

export const loadChatInfo = createAction(
  'WIDGET_CHAT_LOAD_INFO',
  chatId => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    return new Promise(resolve => {
      DpApi
        .sendGet(`DP_API/chats/${chatId}/polling?${queryParams}`, {...ajaxOptions})
        .success(response => resolve(response.chat_info && response.chat_info.data));
    });
  }
);

export const pollingChat = createAction(
  'WIDGET_CHAT_POLLING',
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const queryParams = compileParams(addSessionCode(state, params));

    const promise = DpApi.sendGet(`DP_API/chats/${chatId}/polling?${queryParams}`, {...ajaxOptions});
    promise.success(response => {
      const locked = lockedPollingSelector(state);
      const skipped = skippedPollingSelector(state);

      // Waiting for ajax response
      if (locked) {
        return;
      }

      // Waiting for next response to get actual data
      if (skipped) {
        dispatch(enablePollingResponse());
        return;
      }

      const oldChatInfo = chatInfoSelector(state);
      const newChatInfo = response.chat_info && response.chat_info.data;
      const loaded = chatLoadedSelector(state);

      if (newChatInfo) {
        // Chat info was changed
        if (!oldChatInfo.equals(Immutable.fromJS(newChatInfo))) {
          // Update chat info
          dispatch(updateChatInfo(newChatInfo));

          // Handle chat transcript
          const transcriptChecked = transcriptCheckedSelector(state);
          if (newChatInfo.author_email) {
            // Auto select transcript checkbox if user has email
            if (!transcriptChecked) {
              dispatch(enableSendTranscript());
            } else {
              // If can send transcript data and chat is ended
              const transcriptSending = transcriptSendingSelector(state);
              const transcriptSent = transcriptSentSelector(state);

              if (newChatInfo.date_ended && !transcriptSending && !transcriptSent) {
                // send transcript data
                dispatch(sendTranscriptData(chatId));
              }
            }
          } else {
            if (transcriptChecked) {
              dispatch(disableSendTranscript());
            }
          }
        }

        // Toggle reopen chat
        const canReopen = canReopenSelector(state);
        if (!newChatInfo.date_ended) {
          if (!canReopen) {
            dispatch(enableChatReopen());
          }
        } else {
          const ended = moment(newChatInfo.date_ended).format('X');
          const now = moment().format('X');
          const delay = ended - now + 120; // can reopen in 2 minutes

          if (canReopen && delay < 0) {
            dispatch(disableChatReopen());
          }
        }
      }

      // Received new messages
      const newMessages = response.new_messages ? response.new_messages.data : [];
      if (newMessages.length) {
        const existMessageIds = messageIdsSelector(state);
        const filteredMessages = newMessages
          // Skip user's messages because they are added optimistically,
          // but do load user's messages on initial polling request
          .filter(message => !loaded || (loaded && message.author_type !== 'user'))
          // Check for unique ids
          .filter(message => existMessageIds.indexOf(message.id) === -1);

        if (filteredMessages.length) {
          dispatch(addNewMessages(filteredMessages));
        }

        // Filter not acked messages and send ack request
        const ackMessages = filteredMessages.filter(message => message.author_type === 'agent' && !message.date_received);
        if (ackMessages.length) {
          dispatch(ackChatMessages(chatId, {message_ids: ackMessages.map(message => message.id)}));
        }
      }

      // Mark chat as loaded on first polling response
      if (!loaded) {
        dispatch(setLoaded());
      }
    });

    return promise;
  }
);

export const sendUserTyping = createAction(
  'WIDGET_CHAT_SEND_USER_TYPING',
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    return DpApi.sendPost(`DP_API/chats/${chatId}/user_typing?${queryParams}`, params, {...ajaxOptions});
  }
);

export const sendChatMessage = createAction(
  'WIDGET_CHAT_SEND_MESSAGE',
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const authorAvatar = authorAvatarSelector(state);
    const tmpId = generate({
      length: 20,
      charset: 'alphabetic'
    });

    // Add optimistic message
    if (striptags(params.message)) {
      dispatch(addNewMessages({
        tmp_id: tmpId,
        content: params.message,
        is_html: true,
        author_avatar: authorAvatar,
        author_type: 'user',
        date_created: moment().format()
      }));
    }

    // Add optimistic attachments
    const attachments = attachmentsSelector(state);
    params.attachments.forEach(blobAuthId => {
      const attachment = attachments.filter(blob => blob.get('blob_auth_id') === blobAuthId).first();
      dispatch(addNewMessages({
        tmp_id: tmpId,
        content: null,
        is_html: true,
        author_avatar: authorAvatar,
        author_type: 'user',
        date_created: moment().format(),
        metadata: {
          type: 'file',
          blob_id: attachment.get('blob_id'),
          blob: attachment
        }
      }));
    });

    // Reset attachments after send
    dispatch(resetAttachments());

    const queryParams = compileParams(addSessionCode(state));
    const promise = DpApi.sendPost(`DP_API/chats/${chatId}/messages?${queryParams}`, params, {...ajaxOptions});
    promise.catch(() => {
      dispatch(markNotDelivered(tmpId));
    });

    return promise;
  }
);

export const endChat = createAction(
  'WIDGET_CHAT_END',
  chatId => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    dispatch(lockPollingResponse());

    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    const promise = DpApi.sendPost(`DP_API/chats/${chatId}/end?${queryParams}`, null, {...ajaxOptions});
    promise.success(() => {
      dispatch(unlockPollingResponse());
      localStorage.removeItem('dpWidget.chat.chatId');
    });
    promise.catch(() => dispatch(unlockPollingResponse()));

    return promise;
  }
);

export const reopenChat = createAction(
  'WIDGET_CHAT_REOPEN',
  chatId => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    dispatch(lockPollingResponse());

    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    const promise = DpApi.sendPost(`DP_API/chats/${chatId}/reopen?${queryParams}`, null, {...ajaxOptions});
    promise.success(() => {
      dispatch(unlockPollingResponse());
      localStorage.setItem('dpWidget.chat.chatId', chatId);
    });
    promise.catch(() => dispatch(unlockPollingResponse()));

    return promise;
  }
);

export const sendFeedback = createAction(
  'WIDGET_CHAT_SEND_FEEDBACK',
  (chatId, params) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const queryParams = compileParams(addSessionCode(state));

    return DpApi.sendPost(`DP_API/chats/${chatId}/feedback?${queryParams}`, params, {...ajaxOptions});
  }
);
