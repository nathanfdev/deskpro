import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import { setFullPayload, setValue, toggleBool, async } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';

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
    {id: 104, content: 'my message my message my message my message :)', author: null, is_sys: false, is_html: true, date_created: '2015-12-03 14:10'},
    {id: 103, content: 'agent reply agent reply agent reply agent reply agent reply agent reply ;)', author: 1, author_type: 'agent', is_sys: false, date_created: '2015-12-03 14:02'},
    {id: 102, content: '{"phrase_id":"message_assigned","name":"Admin Admin"}', author: null, author_type: 'user', is_sys: true, date_created: '2015-12-03 13:58'},
    {id: 101, content: '{"phrase_id":"message_started"}', author: null, is_sys: true, date_created: '2015-12-03 13:50'}
  ]
};

export default createReducer(initialState, {
  [actions.toggleAudioNotifications]: toggleBool('audioNotifications'),
  [actions.setChatId]: setFullPayload('chatId'),
  [actions.updateChatInfo]: setFullPayload('chatInfo'),
  [actions.reopenChat]: setValue('chat.date_ended', null),

  // Messages
  [actions.resetMessages]: setValue('messages', []),
  [actions.addNewMessages]: (state, payload) => {
    const oldMessages = state.get('messages', Immutable.fromJS([])).toJS();
    return state.set('messages', Immutable.fromJS(oldMessages.concat(payload)));
  },

  // Transcript
  [actions.disableSendTranscript]: setValue('transcript.checked', false),
  [actions.enableSendTranscript]: setValue('transcript.checked', true),
  [actions.resetTranscriptDataSent]: setValue('sent.checked', false),
  [actions.sendTranscriptData]: async({
    success: setValue('transcript.sent', true),
    start: setValue('transcript.sending', true),
    done: setValue('transcript.sending', true)
  })
});
