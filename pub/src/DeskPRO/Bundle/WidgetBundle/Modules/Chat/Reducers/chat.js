import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/chatActions';
import { async, setFullPayload, setValue } from 'Ampliflux/reducers/handlers';

const initialState = {
  chat: null,
  async: {
    createChat: false
  }
};

export default createReducer(initialState, {
  [actions.createChat]: async({
    success: setFullPayload('chat'),
    start: setValue('async.createChat', false),
    done: setValue('async.createChat', true)
  })
});
