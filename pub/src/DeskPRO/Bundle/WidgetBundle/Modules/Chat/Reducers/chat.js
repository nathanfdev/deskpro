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
  mute: false,
  transcript: {
    checked: false,
    sending: false,
    sent: true
  },
  chatId: null,
  chatInfo: {},
  messages: [
    {id: 104, content: '1', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '2', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '3', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '4', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '5', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '6', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '7', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '8', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '9', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '10', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '11', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '12', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '13', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '14', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '15', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '16', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '17', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '18', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '19', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 104, content: '20', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'}
  ],
  uploading: {
    files: [],
    failed: [],
    repeat: []
  },
  attachments: []
};

export default createReducer(initialState, {
  // Audio
  [actions.toggleMute]: toggleBool('mute'),

  // Chat setup
  [actions.setChatId]: setFullPayload('chatId'),
  [actions.updateChatInfo]: setFullPayload('chatInfo'),
  [actions.reopenChat]: mergeValue(null, {chatInfo: {date_ended: null}, transcript: {sent: false}}, true),

  // Messages
  [actions.resetMessages]: setValue('messages', []),
  [actions.addNewMessage]: pushPayloadToCollection('messages'),

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
  [actions.resetTranscriptDataSent]: setValue('transcript.sent', false),
  [actions.sendTranscriptData]: async({
    success: setValue('transcript.sent', true),
    start: setValue('transcript.sending', true),
    done: setValue('transcript.sending', false)
  })
});
