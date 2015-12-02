import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import { async, setFullPayload, setValue } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';
import moment from 'moment';

const initialState = {
  chat: {
    messages: [
      {id: 100, message: 'my message my message my message my message', type: 'agent'},
      {id: 101, message: '{"phrase_id":"message_assigned","name":"Admin Admin"}', type: 'agent'}
    ],
    date_ended: moment().format('X')
  }
};

export default createReducer(initialState, {
  [actions.createChat]: async({
    success: setFullPayload('chat')
  }),
  [actions.pollingChat]: async({
    success: (state, payload, action) => {
      const oldMessages = state.getIn(['chat', 'messages'], Immutable.fromJS([])).toJS();
      payload.messages = oldMessages.concat(payload.messages);

      return setFullPayload('chat')(state, payload, action);
    }
  }),
  [actions.reopenChat]: setValue('chat.date_ended', null)
});
