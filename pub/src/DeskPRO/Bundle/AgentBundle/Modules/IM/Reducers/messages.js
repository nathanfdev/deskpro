import * as actions from '../Actions/messagesActions';
import { createReducer } from 'Ampliflux';
import { async } from 'Ampliflux/reducers/handlers';
import Immutable from 'immutable';

const initialState = {
  chatMessages: {},
  searchMessages: {},
  loadingMessages: true,
  counts: [],
  countsLoading: true
};
export default createReducer(initialState, {
  [actions.loadMessages]: async(
    {
      start: (state) => state.set('loadingMessages', true),
      success: (state, payload) => {
        let path;
        if (payload.searchQuery) {
          path = ['searchMessages', payload.chat_id];
        } else {
          path = ['chatMessages', payload.chat_id];
        }
        if (!state.getIn(path) || (payload.searchQuery && state.getIn(path).searchQuery !== payload.searchQuery)) {
          return state.setIn(path, payload);
        }
        // OMG!!!
        const messages = state.getIn(path).messages;
        const page = state.getIn(path).page;
        const union = {};
        messages.map((message) => union[message.id] = message);
        payload.messages.map((message) => union[message.id] = message);
        const obj = {
          messages: new Immutable.Map(union).toArray(),
          page: Math.max(page, payload.page),
          pages: payload.pages
        };
        return state.setIn(path, obj);
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
