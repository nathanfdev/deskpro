import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import { async, mergeFullPayload } from 'Ampliflux/reducers/handlers';

const initialState = {
  chat: {
    messages: []
  }
};

export default createReducer(initialState, {
  [actions.createChat]: async({
    success: mergeFullPayload('chat')
  }),
  [actions.pollingChat]: async({
    success: mergeFullPayload('chat')
  })
});
