import * as actions from '../Actions/search';
import { createReducer } from 'Ampliflux';
import Immutable from 'immutable';
import { setValue, async } from 'Ampliflux/reducers/handlers';
import invariant from 'invariant';

const initialState = {
  results: {
    ticket: [],
    article: [],
    chat_conversation: []
  },
  searching: false
};

export default createReducer(initialState, {
  [actions.quickSearchResetAction]: (state) => state.set('results', Immutable.fromJS({
    ticket: [],
    article: [],
    chat_conversation: []
  })),
  [actions.quickSearchAction]: async({
    success: (state, payload) => {
      if (!payload) return;
      invariant(payload.data && payload.data.grouped_results, 'Invalid payload');

      const results = payload.data.grouped_results || [];

      for (let i = 0; i < results.length; i++) {
        const group = results[i];
        state = state.setIn(['results', group.type], Immutable.fromJS(group.results));
      }

      return state;
    },
    start: setValue('searching', true),
    done: setValue('searching', false)
  })
});
