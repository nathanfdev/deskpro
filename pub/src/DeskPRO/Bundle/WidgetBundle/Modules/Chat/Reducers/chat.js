import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import { async, setFullPayload } from 'Ampliflux/reducers/handlers';

const initialState = {
  chat: {}
};

export default createReducer(initialState, {
  [actions.createChat]: async({
    success: setFullPayload('chat')
  }),
  [actions.pollingChat]: async({
    success: setFullPayload('chat')
  })
});
