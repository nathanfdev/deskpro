import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import { setFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import moment from 'moment';
import Immutable from 'immutable';

const initialState = {
  chatInfo: {
    date_ended: moment().format('X')
  },
  messages: [
    {id: 100, content: 'my message my message my message my message', type: 'agent'},
    {id: 101, content: '{"phrase_id":"message_assigned","name":"Admin Admin"}', type: 'agent'}
  ]
};

export default createReducer(initialState, {
  [actions.updateChatInfo]: setFullPayload('chatInfo'),
  [actions.addNewMessages]: (state, payload) => {
    const oldMessages = state.get('messages', Immutable.fromJS([])).toJS();
    return state.set('messages', Immutable.fromJS(oldMessages.concat(payload)));
  },
  [actions.reopenChat]: setValue('chat.date_ended', null)
});
