import * as actions from '../Actions/messagesActions';
import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';

const initialState = {
  chatMessages: {},
  loadingMessages: true,
  counts: {},
  countsLoading: true
};
export default createReducer(initialState, {
  [actions.loadMessages]: async(
    {
      begin: (state) => state.set('loadingMessages', true),
      success: (state, payload) => {
        return state.setIn(['chatMessages', payload.chat_id], payload.messages);
      },
      done: (state) => state.set('loadingMessages', false)
    }
  ),
  [actions.addMessageOptimistic]: async({
    success: (state, payload) => {
      // updateIn works well enough, but not causes components rerender
      const messages = state.getIn(['chatMessages', payload.chat_id]).push(payload.message);
      return state.set(['chatMessages', payload.chat_id], messages);
    }
  }),
  [actions.refreshCounts]: async({
    success: (state, payload) => state.set('counts', payload),
    begin: (state) => state.set('countsLoading', true),
    done: (state) => state.set('countsLoading', false)
  })
});
