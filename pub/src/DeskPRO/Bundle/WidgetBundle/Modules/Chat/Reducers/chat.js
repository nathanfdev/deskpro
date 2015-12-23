import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import {
  setFullPayload,
  setValue,
  setValueOnError,
  toggleBool,
  async,
  pushPayloadToCollection,
  deletePayloadFromCollection,
  composeHandlers
} from 'Ampliflux/reducers/handlers';

const initialState = {
  phrases: {},
  mute: false,
  transcript: {
    checked: false,
    saving: false,
    sending: false,
    sent: false
  },
  chatId: null,
  chatLoaded: false,
  chatInfo: {},
  messages: [],
  uploading: {
    files: [],
    failed: [],
    repeat: []
  },
  attachments: []
};

export default createReducer(initialState, {
  // Phrase translations
  [actions.setPhraseTranslations]: setFullPayload('phrases'),

  // Audio
  [actions.toggleMute]: toggleBool('mute'),

  // Chat setup
  [actions.setChatId]: composeHandlers(
    setFullPayload('chatId'),
    setValue('chatLoaded', false),
    setValue('messages', []),
    setValue('transcript.sent', false)
  ),
  [actions.setLoaded]: setValue('chatLoaded', true),
  [actions.updateChatInfo]: setFullPayload('chatInfo'),
  [actions.reopenChat]: composeHandlers(
    setValue('chatInfo.date_ended', null),
    setValue('transcript.sent', false)
  ),

  // Messages
  [actions.addNewMessages]: pushPayloadToCollection('messages'),
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
  [actions.addUploadingFile]: pushPayloadToCollection('uploading.files', true),
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
  [actions.addAttachment]: pushPayloadToCollection('attachments'),
  [actions.removeAttachment]: deletePayloadFromCollection('attachments'),
  [actions.resetAttachments]: setValue('attachments', []),

  // Transcript
  [actions.disableSendTranscript]: setValue('transcript.checked', false),
  [actions.enableSendTranscript]: setValue('transcript.checked', true),
  [actions.sendTranscriptInfo]: async({
    start: setValue('transcript.saving', true),
    done: setValue('transcript.saving', false),
    error: setValueOnError('transcript.saving', false)
  }),
  [actions.sendTranscriptData]: async({
    success: setValue('transcript.sent', true),
    start: setValue('transcript.sending', true),
    done: setValue('transcript.sending', false),
    error: setValueOnError('transcript.sending', false)
  })
});
