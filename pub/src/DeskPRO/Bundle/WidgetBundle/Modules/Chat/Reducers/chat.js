import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import { async, setFullPayload } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';
import moment from 'moment';

const initialState = {
  chat: {
    messages: [],
    date_ended: moment().format()
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
  })
});
