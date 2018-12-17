import { generate } from 'randomstring';
import striptags from 'striptags';
import moment from 'moment';
import Immutable from 'immutable';
import linkifyHtml from 'linkifyjs/html';
import $ from 'jquery';
import { createAction } from 'DeskPRO/Component/Ampliflux';
import { widgetApi } from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { loadBatch } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { storageAvailable } from 'DeskPRO/Component/Util/storageAvailable';
import { ajaxOptions } from '../../Application/Actions/bootstrapActions';
import { history } from '../../../Services/history';
import {
  skippedPollingSelector,
  lockedPollingSelector,
  chatInfoSelector,
  chatLoadedSelector,
  authorIdSelector,
  messageIdsSelector,
  attachmentsSelector,
  canReopenSelector,
  lastAgentIdSelector
} from '../Selectors/chat';
import { jwtTokenSelector } from '../../Application/Selectors/dpWindow';

const visitorTrack = window.DP_SEND_VISITOR_TRACK;
const visitorId    = visitorTrack && visitorTrack.visitorId ? visitorTrack.visitorId : '';

// Chat setup actions
export const setChatId = createAction('WIDGET_CHAT_SET_ID');
export const unsetChatId = createAction(
  'WIDGET_CHAT_UNSET_ID',
  () => {
    if (storageAvailable('localStorage')) {
      localStorage.removeItem('dpWidget.chat.id');
      localStorage.removeItem('dpWidget.chat.partial');
      localStorage.removeItem('dpWidget.chat.lastAgentId');
    }

    history.replace('/');
  }
);

export const setLastAgentId = createAction(
  'WIDGET_CHAT_SET_LAST_AGENT',
  (lastAgentId) => {
    if (storageAvailable('localStorage')) {
      localStorage.setItem('dpWidget.chat.lastAgentId', lastAgentId);
    }

    return lastAgentId;
  }
);

export const setLoaded = createAction('WIDGET_CHAT_SET_LOADED');
export const unsetLoaded = createAction('WIDGET_CHAT_UNSET_LOADED');
export const updateChatInfo = createAction(
  'WIDGET_CHAT_UPDATE_CHAT_INFO',
  chatInfo => (dispatch, getState) => {
    const state = getState();
    const lastAgentId = lastAgentIdSelector(state);
    const peopleIds = [];

    if (chatInfo.person) {
      peopleIds.push(chatInfo.person);
    }
    if (chatInfo.agent) {
      peopleIds.push(chatInfo.agent);

      if (lastAgentId !== chatInfo.agent) {
        dispatch(setLastAgentId(chatInfo.agent));
      }
    }
    if (lastAgentId && lastAgentId !== chatInfo.agent) {
      peopleIds.push(lastAgentId);
    }

    if (peopleIds.length) {
      dispatch(loadBatch('Person', peopleIds, 'all'));
    }

    return chatInfo;
  }
);

export const optimisticToggleSendTranscript = createAction('WIDGET_CHAT_OPTIMISTIC_TOGGLE_SEND_TRANSCRIPT');
export const enableChatReopen = createAction('WIDGET_CHAT_ENABLE_REOPEN');
export const disableChatReopen = createAction('WIDGET_CHAT_DISABLE_REOPEN');

// Polling actions
export const lockPollingResponse = createAction('WIDGET_LOCK_POLLING_RESPONSE');
export const unlockPollingResponse = createAction('WIDGET_UNLOCK_POLLING_RESPONSE');
export const enablePollingResponse = createAction('WIDGET_ENABLE_POLLING_RESPONSE');

// Audio actions
export const toggleMute = createAction('WIDGET_CHAT_TOGGLE_MUTE');

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
export const createChat = createAction(
  'WIDGET_CHAT_CREATE_NEW',
  params => (dispatch, getState) => {
    const state = getState();
    const jwt = jwtTokenSelector(state);

    return widgetApi
      .sendPost(`DP_API/chats/create?dp__v=${visitorId}`, { ...params, jwt }, { ...ajaxOptions(state) })
      .success((response) => {
        const data = response.data || {};
        const chatId = data.auth_id;

        if (chatId) {
          if (storageAvailable('sessionStorage')) {
            sessionStorage['dpWidget.chat.id'] = chatId;
            sessionStorage.removeItem('dpWidget.chat.lastAgentId');
          }

          dispatch(setChatId(chatId));
        }
      });
  }
);

export const ackChatMessages = createAction(
  'WIDGET_CHAT_ACK_MESSAGES',
  (chatId, params) => (dispatch, getState) => {
    const state = getState();
    if (!chatId) {
      return null;
    }

    return widgetApi.sendPost(`DP_API/chats/${chatId}/ack_messages`, params, { ...ajaxOptions(state) });
  }
);

export const toggleSendTranscript = createAction(
  'WIDGET_CHAT_TOGGLE_SEND_TRANSCRIPT',
  (chatId, value) => (dispatch, getState) => {
    const state = getState();
    if (!chatId) {
      return null;
    }

    dispatch(lockPollingResponse());
    dispatch(optimisticToggleSendTranscript(value));

    const params  = { should_send_transcript: value };
    const promise = widgetApi.sendPost(`DP_API/chats/${chatId}/transcript/toggle`, params, { ...ajaxOptions(state) });
    promise.success(() => dispatch(unlockPollingResponse()));
    promise.catch(() => dispatch(unlockPollingResponse()));

    return promise;
  }
);

export const sendTranscriptInfo = createAction(
  'WIDGET_CHAT_SEND_TRANSCRIPT_INFO',
  (chatId, params) => (dispatch, getState) => {
    const state = getState();
    if (!chatId) {
      return null;
    }

    dispatch(lockPollingResponse());

    const promise = widgetApi.sendPost(`DP_API/chats/${chatId}/transcript/info`, params, { ...ajaxOptions(state) });
    promise.success(() => dispatch(unlockPollingResponse()));
    promise.catch(() => dispatch(unlockPollingResponse()));

    return promise;
  }
);

export const pollingChat = createAction(
  'WIDGET_CHAT_POLLING',
  (chatId, params) => (dispatch, getState) => {
    const state = getState();
    if (!chatId) {
      return null;
    }

    const queryParams = compileParams(params);
    const promise = widgetApi.sendGet(`DP_API/chats/${chatId}/polling?${queryParams}`, { ...ajaxOptions(state) });
    promise.success((response) => {
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
        }

        // Chat closed by user can be reopened for 5 minutes, chat are closed immediately otherwise
        const canReopen = canReopenSelector(state);
        if (!newChatInfo.date_ended && !canReopen) {
          dispatch(enableChatReopen());
        }

        if (newChatInfo.date_ended && canReopen) {
          if (newChatInfo.ended_by === 'user') {
            const ended = moment(newChatInfo.date_ended).format('X');
            const now = moment().format('X');
            const delay = (ended - now) + 300; // can reopen in 5 minutes

            if (delay < 0) {
              dispatch(disableChatReopen());
            }
          } else {
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
          .filter(message => !loaded || (loaded && !message.is_user))
          // Check for unique ids
          .filter(message => existMessageIds.indexOf(message.id) === -1);

        if (filteredMessages.length) {
          dispatch(addNewMessages(filteredMessages));
        }

        // Filter not acked messages and send ack request
        const ackMessages = filteredMessages.filter(message => !message.is_sys && !message.is_user && !message.date_received);
        if (ackMessages.length) {
          dispatch(ackChatMessages(chatId, { message_ids: ackMessages.map(message => message.id) }));
        }

        // Load person info
        const peopleIds = [];
        filteredMessages.forEach((message) => {
          const authorId = message.author;
          if (authorId && peopleIds.indexOf(authorId) === -1) {
            peopleIds.push(authorId);
          }
        });

        if (peopleIds.length) {
          dispatch(loadBatch('Person', peopleIds, 'all'));
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
    const state = getState();
    if (!chatId) {
      return null;
    }

    return widgetApi.sendPost(`DP_API/chats/${chatId}/user_typing`, params, { ...ajaxOptions(state) });
  }
);

export const savePartialTyping = createAction(
  'WIDGET_CHAT_SAVE_PARTIAL_TYPING',
  (params) => {
    if (storageAvailable('localStorage')) {
      localStorage.setItem('dpWidget.chat.partial', params);
    }
  }
);

export const sendChatMessage = createAction(
  'WIDGET_CHAT_SEND_MESSAGE',
  (chatId, rawParams) => (dispatch, getState) => {
    if (!chatId) {
      return null;
    }

    const state = getState();
    const authorId = authorIdSelector(state);
    const tmpId = generate({
      length:  20,
      charset: 'alphabetic'
    });

    // prepare params
    const params = $.extend(true, {}, rawParams);

    // prepare message content
    if (params.message) {
      params.message = linkifyHtml(params.message);
      params.message = params.message.replace(/(&nbsp;|\s)+$/g, '');
      params.message = params.message.replace(/^(&nbsp;|\s)+/g, '');
    }

    // don't send empty messages
    if (!striptags(params.message) && (!params.attachments || !params.attachments.size)) {
      return null;
    }

    // Add optimistic message
    if (params.message && striptags(params.message)) {
      dispatch(addNewMessages({
        tmp_id:       tmpId,
        content:      params.message,
        is_html:      true,
        is_user:      true,
        author:       authorId,
        date_created: moment().format()
      }));
    }

    // Add optimistic attachments
    const attachments = attachmentsSelector(state);
    params.attachments.forEach((blobAuthId) => {
      const attachment = attachments.filter(blob => blob.get('blob_auth_id') === blobAuthId).first();
      dispatch(addNewMessages({
        tmp_id:       tmpId,
        content:      null,
        is_html:      true,
        is_user:      true,
        author:       authorId,
        date_created: moment().format(),
        metadata:     {
          type:    'file',
          blob_id: attachment.get('blob_id'),
          blob:    attachment
        }
      }));

      dispatch(removeAttachment(attachment));
    });

    const promise = widgetApi.sendPost(`DP_API/chats/${chatId}/messages`, params, { ...ajaxOptions(state) });
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

    const state   = getState();
    const promise = widgetApi.sendPost(`DP_API/chats/${chatId}/end`, null, { ...ajaxOptions(state) });
    promise.success(() => {
      dispatch(unlockPollingResponse());
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

    const state   = getState();
    const promise = widgetApi.sendPost(`DP_API/chats/${chatId}/reopen`, null, { ...ajaxOptions(state) });
    promise.success(() => {
      dispatch(unlockPollingResponse());
    });
    promise.catch(() => dispatch(unlockPollingResponse()));

    return promise;
  }
);

export const sendFeedback = createAction(
  'WIDGET_CHAT_SEND_FEEDBACK',
  (chatId, params) => (dispatch, getState) => {
    const state = getState();
    if (!chatId) {
      return null;
    }

    return widgetApi.sendPost(`DP_API/chats/${chatId}/feedback`, params, { ...ajaxOptions(state) });
  }
);

export const findAnotherAgent = createAction(
  'WIDGET_CHAT_FIND_ANOTHER_AGENT',
  (chatId, params) => (dispatch, getState) => {
    const state = getState();
    if (!chatId) {
      return null;
    }

    return widgetApi.sendPost(`DP_API/chats/${chatId}/find_agent`, params, { ...ajaxOptions(state) });
  }
);

