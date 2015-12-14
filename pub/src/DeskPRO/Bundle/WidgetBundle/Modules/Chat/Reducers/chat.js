import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import {
  setFullPayload,
  setValue,
  mergeValue,
  toggleBool,
  async,
  pushPayloadToCollection,
  deletePayloadFromCollection
} from 'Ampliflux/reducers/handlers';

const initialState = {
  audioNotifications: true,
  transcript: {
    checked: false,
    sending: false,
    sent: true
  },
  chatId: null,
  chatInfo: {
    date_ended: null,
    author_name: 'User',
    author_email: 'email@mail.com'
  },
  messages: [
    {id: 105, content: 'File: <a href="#">Some file</a>', author: null, author_type: 'user', is_sys: false, is_html: true, metadata: {type: 'file', blob: {is_image: true}}, date_created: '2015-12-03 14:10'},
    {id: 104, content: 'my message my message my message my message :)', author: null, author_type: 'user', is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 103, content: 'agent reply agent reply agent reply agent reply agent reply agent reply ;)', author: 1, author_type: 'agent', is_sys: false, date_created: '2015-12-03 14:02'},
    {id: 102, content: '{"phrase_id":"message_assigned","name":"Admin Admin"}', author: null, author_type: 'user', is_sys: true, date_created: '2015-12-03 13:58'},
    {id: 101, content: '{"phrase_id":"message_started"}', author: null, is_sys: true, date_created: '2015-12-03 13:50'}
  ],
  attachments: []
};

export default createReducer(initialState, {
  // Controls
  [actions.toggleAudioNotifications]: toggleBool('audioNotifications'),

  // Chat setup
  [actions.setChatId]: setFullPayload('chatId'),
  [actions.updateChatInfo]: setFullPayload('chatInfo'),
  [actions.reopenChat]: mergeValue(null, {chatInfo: {date_ended: null}, transcript: {sent: false}}, true),

  // Messages
  [actions.resetMessages]: setValue('messages', []),
  [actions.addNewMessage]: pushPayloadToCollection('messages'),

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
