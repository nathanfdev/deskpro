import { createReducer } from 'DeskPRO/Component/Ampliflux';
import {
  async,
  setFullPayload,
  setValue,
  toggleBool,
  pushPayloadToCollection,
  deletePayloadFromCollection,
  composeHandlers
} from 'DeskPRO/Component/Ampliflux/reducers/handlers';
import moment from 'moment';
import * as actions from '../Actions/chatActions';

const initialState = {
  mute:    false,
  polling: {
    locked:  false,
    skipped: false
  },
  chat: {
    id:        null,
    loaded:    false,
    canReopen: true,
    info:      {},
    lastAgent: null
  },
  uploading: {
    files:  [],
    failed: [],
    repeat: []
  },
  messages:       [],
  attachments:    [],
  feedbackStage:  'dialog',
  lostConnection: false
};

let connectionRetries = 0;

export default createReducer(initialState, {
  // Polling
  [actions.lockPollingResponse]:   setValue('polling.locked', true),
  [actions.unlockPollingResponse]: setValue('polling', { locked: false, skipped: true }),
  [actions.enablePollingResponse]: setValue('polling', { locked: false, skipped: false }),

  // Audio
  [actions.toggleMute]: toggleBool('mute'),

  // Chat setup
  [actions.setChatId]: composeHandlers(
    setFullPayload('chat.id'),
    setValue('chat.loaded', false),
    setValue('chat.info', {}),
    setValue('chat.lastAgent', null),
    setValue('messages', []),
    setValue('feedbackStage', 'dialog')
  ),
  [actions.unsetChatId]: setValue('chat', {
    id:        null,
    loaded:    false,
    canReopen: true,
    info:      {},
    lastAgent: null
  }),
  [actions.setLoaded]:      setValue('chat.loaded', true),
  [actions.unsetLoaded]:    setValue('chat.loaded', false),
  [actions.updateChatInfo]: setFullPayload('chat.info'),
  [actions.setLastAgentId]: setFullPayload('chat.lastAgent'),

  [actions.optimisticToggleSendTranscript]: setFullPayload('chat.info.should_send_transcript'),

  [actions.sendTranscriptInfo]: async({
    done: setValue('chat.info.should_send_transcript', true)
  }),

  [actions.endChat]:    setValue('chat.info.date_ended', moment().format()),
  [actions.reopenChat]: composeHandlers(
    setValue('chat.canReopen', true),
    setValue('chat.info.date_ended', null),
    setValue('chat.info.date_transcript_sent', null),
    setValue('feedbackStage', 'dialog')
  ),

  [actions.enableChatReopen]:  setValue('chat.canReopen', true),
  [actions.disableChatReopen]: setValue('chat.canReopen', false),

  // Messages
  [actions.addNewMessages]:   pushPayloadToCollection('messages'),
  [actions.markNotDelivered]: (state, tmpId) => {
    let newMessages = state.get('messages');

    const sendingMessages = newMessages.filter(message => message.get('tmp_id') === tmpId);
    sendingMessages.forEach(sendingMessage => {
      const index = newMessages.indexOf(sendingMessage);
      const newMessage = sendingMessage.set('not_delivered', true);

      newMessages = newMessages.set(index, newMessage);
    });

    return state.set('messages', newMessages);
  },

  // Uploading files
  [actions.addUploadingFile]:        pushPayloadToCollection('uploading.files', true),
  [actions.markUploadingFileFailed]: composeHandlers(
    deletePayloadFromCollection('uploading.repeat'),
    pushPayloadToCollection('uploading.failed', true)
  ),
  [actions.repeatUploadingFile]: composeHandlers(
    deletePayloadFromCollection('uploading.failed'),
    pushPayloadToCollection('uploading.repeat', true)
  ),
  [actions.removeUploadingFile]: composeHandlers(
    deletePayloadFromCollection('uploading.files'),
    deletePayloadFromCollection('uploading.failed'),
    deletePayloadFromCollection('uploading.repeat')
  ),

  // Attachments
  [actions.addAttachment]:    pushPayloadToCollection('attachments'),
  [actions.removeAttachment]: deletePayloadFromCollection('attachments'),
  [actions.resetAttachments]: setValue('attachments', []),

  // Feedback
  [actions.showNotHelpfulForm]: setValue('feedbackStage', 'form'),
  [actions.sendFeedback]:       setValue('feedbackStage', 'finished'),

  [actions.pollingChat]: async({
    success: state => {
      connectionRetries = 0;
      return state.set('lostConnection', false);
    },
    error: state => {
      connectionRetries++;
      return connectionRetries > 2
        ? state.set('lostConnection', true)
        : state;
    },
  })
});
