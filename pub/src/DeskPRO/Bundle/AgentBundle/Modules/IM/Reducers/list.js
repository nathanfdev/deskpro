import { createReducer } from 'Ampliflux';
import * as actions from '../Actions/imListActions';
import Immutable from 'immutable';

const initialState = {
  recentAgents: []
};

export default createReducer(initialState, {
  [actions.loadRecentAgents]: (state, payload) => {
    return state.set('recentAgents', payload);
  }
});