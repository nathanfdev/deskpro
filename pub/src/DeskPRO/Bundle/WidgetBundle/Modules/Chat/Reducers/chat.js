import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import {
  setFullPayload,
  setValue,
  mergeValue,
  toggleBool,
  async,
  pushPayloadToCollection,
  deletePayloadFromCollection,
  composeHandlers
} from 'Ampliflux/reducers/handlers';

const initialState = {
  audioNotifications: true,
  transcript: {
    checked: false,
    sending: false,
    sent: true
  },
  chatId: null,
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
  // Audio
  [actions.toggleAudioNotifications]: toggleBool('audioNotifications'),

  // Chat setup
  [actions.setChatId]: setFullPayload('chatId'),
  [actions.updateChatInfo]: setFullPayload('chatInfo'),
  [actions.reopenChat]: mergeValue(null, {chatInfo: {date_ended: null}, transcript: {sent: false}}, true),

  // Messages
  [actions.resetMessages]: setValue('messages', []),
  [actions.addNewMessage]: pushPayloadToCollection('messages'),

  // Uploading files
  [actions.addUploadingFile]: pushPayloadToCollection('uploading.files'),
  [actions.markUploadingFileFailed]: composeHandlers(
    deletePayloadFromCollection('uploading.repeat'),
    pushPayloadToCollection('uploading.failed')
  ),
  [actions.repeatUploadingFile]: composeHandlers(
    deletePayloadFromCollection('uploading.failed'),
    pushPayloadToCollection('uploading.repeat')
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
  [actions.resetTranscriptDataSent]: setValue('transcript.sent', false),
  [actions.sendTranscriptData]: async({
    success: setValue('transcript.sent', true),
    start: setValue('transcript.sending', true),
    done: setValue('transcript.sending', false)
  })
});
