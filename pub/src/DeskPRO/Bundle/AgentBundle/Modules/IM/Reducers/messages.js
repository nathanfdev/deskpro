import * as actions from '../Actions/messagesActions';
import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';

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
        if (!state.getIn(['chatMessages', payload.chat_id])) {
          return state.setIn(['chatMessages', payload.chat_id], payload);
        }
        // OMG!!!
        const messages = state.getIn(['chatMessages', payload.chat_id]).messages;
        const page = state.getIn(['chatMessages', payload.chat_id]).page;
        const union = {};
        messages.map((message) => union[message.id] = message);
        payload.messages.map((message) => union[message.id] = message);
        const obj = {
          messages: new Immutable.Map(union).toArray(),
          page: Math.max(page, payload.page),
          pages: payload.pages
        };
        return state.setIn(['chatMessages', payload.chat_id], obj);
      },
      done: (state) => state.set('loadingMessages', false)
    }
  ),
  [actions.addMessageOptimistic]: async({
    success: (state, payload) => {
      // updateIn works well enough, but not causes components rerender
      // const chatMessages = state.getIn(['chatMessages', payload.chat_id]);
      // chatMessages.messages.push(payload.message);
      // return state.setIn(['chatMessages', payload.chat_id], chatMessages);
      return state;
    }
  }),
  [actions.refreshCounts]: async({
    success: (state, payload) => state.set('counts', payload),
    begin: (state) => state.set('countsLoading', true),
    done: (state) => state.set('countsLoading', false)
  })
});
